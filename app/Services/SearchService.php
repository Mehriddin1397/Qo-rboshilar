<?php

namespace App\Services;

use App\Enums\ContentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;

/**
 * Faza 11 §5-§18: bitta so'rov bilan 5 xil model (Qorboshi/Uzgolon/Literature/Video/Blog)
 * ichidan, faqat published/approved kontentni, LIKE orqali qidiradi va flat (aralash)
 * pagination bilan qaytaradi.
 *
 * Nega "flat merge", "group-by-type" emas? §12 pagination va §13 type-filter aniq talab
 * qilingan; bittalashtirilgan sahifalanish bilan "har sahifada har turdan guruh" ziddiyatli
 * bo'lardi (guruh chegaralari sahifa chegaralari bilan mos kelmaydi). Shu sabab natijalar
 * bitta ro'yxatda (har birida turi badge sifatida) ko'rsatiladi — bu ham §11 formatiga
 * (type/title/slug/url/excerpt) to'liq mos.
 *
 * N+1 xavfsizligi: har bir natija faqat o'z modelining skalyar ustunlaridan (full_name,
 * title va h.k.) tuziladi — hech qanday relation yuklanmaydi, shuning uchun natijalar
 * soni oshsa ham query soni o'zgarmaydi (har turdan bittadan COUNT + bittadan SELECT).
 */
class SearchService
{
    private const PER_PAGE = 15;

    public function search(string $term, ?string $typeFilter, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        $types = $typeFilter !== null
            ? [ContentType::from($typeFilter)]
            : ContentType::cases();

        $counts = [];
        foreach ($types as $type) {
            $counts[$type->value] = $this->typeQuery($type, $term)->count();
        }

        $total = array_sum($counts);
        $page = max(1, Paginator::resolveCurrentPage());
        $skip = ($page - 1) * $perPage;
        $remaining = $perPage;
        $results = [];

        foreach ($types as $type) {
            if ($remaining <= 0) {
                break;
            }

            $count = $counts[$type->value];

            if ($count === 0) {
                continue;
            }

            if ($skip >= $count) {
                $skip -= $count;

                continue;
            }

            $take = min($remaining, $count - $skip);

            foreach ($this->typeQuery($type, $term)->skip($skip)->take($take)->get() as $model) {
                $results[] = $this->formatResult($type, $model);
            }

            $remaining -= $take;
            $skip = 0;
        }

        $paginator = new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
        ]);

        return $paginator->withQueryString();
    }

    private function typeQuery(ContentType $type, string $term): Builder
    {
        $modelClass = $type->model();
        $needle = '%'.mb_strtolower($term).'%';

        return $modelClass::query()
            ->published()
            ->where(function (Builder $query) use ($type, $needle) {
                foreach ($this->searchableColumns($type) as $index => $column) {
                    $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                    $query->{$method}('LOWER('.$column.') LIKE ?', [$needle]);
                }
            })
            ->latest();
    }

    /**
     * @return array<int, string>
     */
    private function searchableColumns(ContentType $type): array
    {
        return match ($type) {
            ContentType::Qorboshi => ['full_name', 'short_description', 'biography'],
            ContentType::Uzgolon => ['name', 'short_description', 'historical_location', 'modern_location'],
            ContentType::Literature => ['title', 'author', 'description'],
            ContentType::Video => ['title', 'description'],
            ContentType::Blog => ['title', 'excerpt', 'content'],
            ContentType::TimelineEvent => ['title', 'description'],
        };
    }

    /**
     * @return array{type: string, type_label: string, title: string, slug: string, url: string, excerpt: string}
     */
    private function formatResult(ContentType $type, Model $model): array
    {
        return [
            'type' => $type->value,
            'type_label' => $type->label(),
            'title' => $this->titleOf($type, $model),
            'slug' => $model->slug,
            'url' => route($type->routeName(), $model->slug),
            'excerpt' => Str::limit((string) $this->excerptOf($type, $model), 160),
        ];
    }

    private function titleOf(ContentType $type, Model $model): string
    {
        return match ($type) {
            ContentType::Qorboshi => $model->full_name,
            ContentType::Uzgolon => $model->name,
            default => $model->title,
        };
    }

    private function excerptOf(ContentType $type, Model $model): ?string
    {
        return match ($type) {
            ContentType::Qorboshi => $model->short_description,
            ContentType::Uzgolon => $model->short_description,
            ContentType::Literature => $model->description,
            ContentType::Video => $model->description,
            ContentType::Blog => $model->excerpt ?: strip_tags((string) $model->content),
            ContentType::TimelineEvent => $model->yearRangeLabel().' — '.$model->description,
        };
    }
}

<?php

declare(strict_types = 1);

namespace App\Modules\PageBuilder\Resources\Blocks;

use App\Models\Event;
use App\Modules\PageBuilder\Resources\Content\Campaigns\CampaignBlockType;
use App\Modules\PageBuilder\Resources\Content\Campaigns\CampaignsBlockType;
use App\Modules\PageBuilder\Resources\Content\Campaigns\EventBlockType;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Resource;

#[MapName(SnakeCaseMapper::class)]
class CampaignSlideshowResource extends Resource implements ResolvesFromRow
{
    public function __construct(
        public readonly ?string $headerText,
        public readonly ?string $buttonText,
        public readonly array $slides,
        public readonly bool $withMargin,
    )
    {
    }

    public static function fromRow(array $data): Resource
    {
        return new self(
            headerText: $data[ 'header_text' ] ?? null,
            buttonText: $data[ 'button_text' ] ?? null,
            slides: self::resolveSlides($data[ 'slides' ] ?? []),
            withMargin: $data[ 'with_margin' ] ?? true,
        );
    }

    private static function resolveSlides(array $rows): array
    {
        $blockTypes = [];
        $entitiesByType = [];

        foreach ([ new CampaignBlockType(), new EventBlockType() ] as $blockType) {

            $type = $blockType->type();
            $blockTypes[ $type ] = $blockType;
            $ids = $blockType->ids($rows);
            $entitiesByType[ $type ] = blank($ids) ? [] : $blockType->fetchEntities($ids);

        }

        $slides = [];

        foreach ($rows as $row) {

            $type = $row[ 'type' ] ?? null;
            $blockType = $blockTypes[ $type ] ?? null;

            if (!$blockType instanceof CampaignsBlockType) {
                continue;
            }

            $id = $blockType->id($row[ 'data' ] ?? []);

            if ($id === null) {
                continue;
            }

            $entity = $entitiesByType[ $type ][ $id ] ?? null;
            $slide = $entity instanceof Event
                ? CampaignSlideshowEventResource::fromEvent($entity)
                : $blockType->map($id, $entitiesByType[ $type ]);

            if ($slide !== null) {
                $slides[] = $slide;
            }

        }

        return $slides;
    }
}

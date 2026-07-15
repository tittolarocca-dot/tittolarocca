<?php

namespace App\Filament\Resources\MemberPhotos\Pages;

use App\Filament\Resources\MemberPhotos\MemberPhotoResource;
use Filament\Resources\Pages\ListRecords;

class ListMemberPhotos extends ListRecords
{
    protected static string $resource = MemberPhotoResource::class;
}

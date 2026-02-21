<x-guest-mobile-card 
    :record="$getRecord()" 
    :editUrl="\App\Filament\Promoter\Resources\Guests\GuestResource::getUrl('edit', ['record' => $getRecord()])"
/>

<x-guest-mobile-card 
    :record="$getRecord()" 
    :editUrl="\App\Filament\Resources\Guests\GuestResource::getUrl('edit', ['record' => $getRecord()])"
/>

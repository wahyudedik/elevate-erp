<x-filament-panels::page>
    <x-slot name="description">
        Manage your company information here.
    </x-slot>

    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions :actions="$this->getFormActions()" />
    </x-filament-panels::form>

    <x-filament::section.heading>
        Company Information
    </x-filament::section.heading>

    {{-- <x-filament::section>
        {{ $this->infolist }}
    </x-filament::section> --}}
</x-filament-panels::page>

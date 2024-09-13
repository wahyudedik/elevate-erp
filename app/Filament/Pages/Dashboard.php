<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab as ComponentsTabsTab;
use Filament\Infolists\Components\Tabs\Tab as TabsTab;
use Filament\Pages\Dashboard\Tab;
use Filament\Resources\Components\Tab as ComponentsTab;

class Dashboard extends \Filament\Pages\Dashboard {
    
        protected function getHeaderWidgets(): array
        {
            return [
                // 
            ];
        }
    
        // protected function getColumns(): int | array
        // {
        //     return 2;
        // }
    
        public function getTabs(): array
        {
            return [
                'Tab 1' => Tabs::make()
                    ->schema([
                        // Add your components for Tab 1 here
                    ]),
                'Tab 2' => Tabs::make()
                    ->schema([
                        // Add your components for Tab 2 here
                    ]),
            ];
        }
    
        protected function getFooterWidgets(): array
        {
            return [
                // Add your footer widgets here
            ];
        }
    
        protected function getHeaderActions(): array
        {
            return [
                // Add your header actions here
            ];
        }
    
}

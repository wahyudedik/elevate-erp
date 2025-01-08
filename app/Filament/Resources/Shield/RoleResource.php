<?php

namespace App\Filament\Resources\Shield;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use BezhanSalleh\FilamentShield\Support\Utils;
use App\Filament\Resources\Shield\RoleResource\Pages;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Forms\ShieldSelectAllToggle;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class RoleResource extends Resource implements HasShieldPermissions
{
    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isScopedToTenant = true;

    protected static ?string $tenantOwnershipRelationshipName = 'company';

    protected static ?string $tenantRelationshipName = 'role';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Hidden::make('company_id')
                                    ->default(Filament::getTenant()->id),
                                Forms\Components\TextInput::make('name')
                                    ->label(__('filament-shield::filament-shield.field.name'))
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('guard_name')
                                    ->label(__('filament-shield::filament-shield.field.guard_name'))
                                    ->default(Utils::getFilamentAuthGuard())
                                    ->nullable()
                                    ->readOnly()
                                    ->maxLength(255),

                                ShieldSelectAllToggle::make('select_all')
                                    ->onIcon('heroicon-s-shield-check')
                                    ->offIcon('heroicon-s-shield-exclamation')
                                    ->label(__('filament-shield::filament-shield.field.select_all.name'))
                                    ->helperText(fn(): HtmlString => new HtmlString(__('filament-shield::filament-shield.field.select_all.message')))
                                    ->dehydrated(fn($state): bool => $state),

                            ])
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ]),
                    ]),
                Forms\Components\Tabs::make('Permissions')
                    ->contained()
                    ->tabs([
                        static::getTabFormComponentForResources(),
                        static::getTabFormComponentForPage(),
                        static::getTabFormComponentForWidget(),
                        // static::getTabFormComponentForCustomPermissions(),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->badge()
                    ->label(__('filament-shield::filament-shield.column.name'))
                    ->formatStateUsing(fn($state): string => Str::headline($state))
                    ->colors(['primary'])
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn($record) => "Role: {$record->name}")
                    ->copyable()
                    ->icon('heroicon-o-user-group')
                    ->iconPosition('before')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('guard_name')
                    ->badge()
                    ->label(__('filament-shield::filament-shield.column.guard_name'))
                    ->tooltip('Guard Name')
                    ->copyable()
                    ->icon('heroicon-o-shield-check')
                    ->iconPosition('before')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->badge()
                    ->label(__('filament-shield::filament-shield.column.permissions'))
                    ->counts('permissions')
                    ->colors(['success'])
                    ->tooltip('Number of Permissions')
                    ->sortable()
                    ->icon('heroicon-o-key')
                    ->iconPosition('before')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament-shield::filament-shield.column.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->tooltip(fn($record) => "Last updated: {$record->updated_at}")
                    ->since()
                    ->icon('heroicon-o-clock')
                    ->iconPosition('before')
                    ->size('lg'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guard_name')
                    ->label(__('filament-shield::filament-shield.column.guard_name'))
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ])
                    ->placeholder('All Guards')
                    ->default(null),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->tooltip('Edit Role')
                    ->modalWidth('lg'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->tooltip('Delete Role')
                    ->modalDescription('Are you sure you want to delete this role? This action cannot be undone.'),
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->tooltip('View Role Details')
                    ->modalWidth('lg'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->icon('heroicon-o-trash')
                    ->modalDescription('Are you sure you want to delete these roles? This action cannot be undone.')
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getCluster(): ?string
    {
        return Utils::getResourceCluster() ?? static::$cluster;
    }

    public static function getModel(): string
    {
        return Utils::getRoleModel();
    }

    public static function getModelLabel(): string
    {
        return __('filament-shield::filament-shield.resource.label.role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-shield::filament-shield.resource.label.roles');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Utils::isResourceNavigationRegistered();
    }

    public static function getNavigationGroup(): ?string
    {
        return Utils::isResourceNavigationGroupEnabled()
            ? __('filament-shield::filament-shield.nav.group')
            : '';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-shield::filament-shield.nav.role.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament-shield::filament-shield.nav.role.icon');
    }

    public static function getNavigationSort(): ?int
    {
        return Utils::getResourceNavigationSort();
    }

    public static function getSlug(): string
    {
        return Utils::getResourceSlug();
    }

    public static function getNavigationBadge(): ?string
    {
        return Utils::isResourceNavigationBadgeEnabled()
            ? strval(static::getEloquentQuery()->count())
            : null;
    }

    public static function isScopedToTenant(): bool
    {
        return Utils::isScopedToTenant();
    }

    public static function canGloballySearch(): bool
    {
        return Utils::isResourceGloballySearchable() && count(static::getGloballySearchableAttributes()) && static::canViewAny();
    }

    // public static function getResourceEntitiesSchema(): ?array
    // {
    //     return collect(FilamentShield::getResources())
    //         ->sortKeys()
    //         ->map(function ($entity) {
    //             $sectionLabel = strval(
    //                 static::shield()->hasLocalizedPermissionLabels()
    //                     ? FilamentShield::getLocalizedResourceLabel($entity['fqcn'])
    //                     : $entity['model']
    //             );

    //             return Forms\Components\Section::make($sectionLabel)
    //                 ->description(fn() => new HtmlString('<span style="word-break: break-word;">' . Utils::showModelPath($entity['fqcn']) . '</span>'))
    //                 ->compact()
    //                 ->schema([
    //                     static::getCheckBoxListComponentForResource($entity),
    //                 ])
    //                 ->columnSpan(static::shield()->getSectionColumnSpan())
    //                 ->collapsible();
    //         })
    //         ->toArray();
    // }

    public static function getResourceEntitiesSchema(): ?array
    {
        $resourceGroups = [

            'Data User' => [
                'App\Filament\Resources\UserResource' => 'Berikan Akses Untuk Daftar User :',
                'App\Filament\Resources\Shield\RoleResource' => 'Berikan Akses Untuk Daftar Role :',
            ],

            'Data Master' => [
                'App\Filament\Resources\BranchResource' => 'Berikan Akses Untuk Daftar Cabang :',
                'App\Filament\Resources\DepartmentResource' => 'Berikan Akses Untuk Daftar Departemen :',
                'App\Filament\Resources\PositionResource' => 'Berikan Akses Untuk Laporan Jabatan :',
            ],
            'Manajemen Keuangan' => [
                'App\Filament\Resources\AccountingResource' => 'Berikan Akses Untuk Daftar Akun :',
                'App\Filament\Resources\JournalEntryResource' => 'Berikan Akses Untuk Daftar Jurnal Harian :',
                'App\Filament\Resources\LedgerResource' => 'Berikan Akses Untuk Daftar Buku Besar :',
                'App\Filament\Resources\TransactionResource' => 'Berikan Akses Untuk Daftar Transaksi :',
                'App\Filament\Resources\FinancialReportResource' => 'Berikan Akses Untuk Daftar Laporan Keuangan :',
                'App\Filament\Resources\BalanceSheetResource' => 'Berikan Akses Untuk Daftar Laporan Neraca :',
                'App\Filament\Resources\CashFlowResource' => 'Berikan Akses Untuk Daftar Arus Kas :',
                'App\Filament\Resources\IncomeStatementResource' => 'Berikan Akses Untuk Daftar Laporan Laba Rugi :',
            ],
            'Manajemen SDM' => [
                'App\Filament\Resources\AttendanceResource' => 'Berikan Akses Untuk Daftar Kehadiran Karyawan :',
                'App\Filament\Resources\LeaveResource' => 'Berikan Akses Untuk Daftar Pengajuan Cuti :',
                'App\Filament\Resources\ShiftResource' => 'Berikan Akses Untuk Daftar Jadwal Shift :',
                'App\Filament\Resources\ScheduleResource' => 'Berikan Akses Untuk Daftar Jadwal Kerja :',
                'App\Filament\Resources\CandidateInterviewResource' => 'Berikan Akses Untuk Daftar Calon Karyawan Yang Interview :',
                'App\Filament\Resources\RecruitmentResource' => 'Berikan Akses Untuk Daftar Rekrutmen :',
                'App\Filament\Resources\EmployeeResource' => 'Berikan Akses Untuk Daftar Karyawan Perusahaan :',
                'App\Filament\Resources\EmployeePositionResource' => 'Berikan Akses Untuk Daftar Histori Jabatan Karyawan :',
                'App\Filament\Resources\ApplicationsResource' => 'Berikan Akses Untuk Daftar CV Calon Karyawan :',
                'App\Filament\Resources\CandidateResource' => 'Berikan Akses Untuk Daftar Calon Karyawan :',
                'App\Filament\Resources\PayrollResource' => 'Berikan Akses Untuk Daftar Gaji Karyawan :',
            ],
            'Manajemen CRM' => [
                'App\Filament\Resources\CustomerResource' => 'Berikan Akses Untuk Daftar Pelanggan :',
                'App\Filament\Resources\CustomerInteractionResource' => 'Berikan Akses Untuk Daftar Interaksi Dengan Pelanggan :',
                'App\Filament\Resources\CustomerSupportResource' => 'Berikan Akses Untuk Daftar Layanan Pelanggan :',
                'App\Filament\Resources\OrderItemResource' => 'Berikan Akses Untuk Daftar Item Pesanan :',
                'App\Filament\Resources\OrderProcessingResource' => 'Berikan Akses Untuk Daftar Pesanan Yang Diproses :',
                'App\Filament\Resources\OrderResource' => 'Berikan Akses Untuk Daftar Pesanan :',
                'App\Filament\Resources\SaleResource' => 'Berikan Akses Untuk Daftar Pesanan Penjualan :',
                'App\Filament\Resources\SaleItemResource' => 'Berikan Akses Untuk Daftar Item Penjualan :',
                'App\Filament\Resources\TicketResponseResource' => 'Berikan Akses Untuk Daftar Respon Tiket Pelanggan :',
            ],
            'Manajemen Stok' => [
                'App\Filament\Resources\InventoryResource' => 'Berikan Akses Untuk Daftar Inventaris :',
                'App\Filament\Resources\InventoryTrackingResource' => 'Berikan Akses Untuk Daftar Inventaris Tracking :',
                'App\Filament\Resources\SupplierResource' => 'Berikan Akses Untuk Daftar Pemasok :',
                'App\Filament\Resources\SupplierTransactionsResource' => 'Berikan Akses Untuk Daftar Transaksi Pemasok :',
                'App\Filament\Resources\ProcurementResource' => 'Berikan Akses Untuk Daftar Pengadaan Barang :',
                'App\Filament\Resources\ProcurementItemResource' => 'Berikan Akses Untuk Daftar Item Pengadaan Barang :',
                'App\Filament\Resources\PurchaseResource' => 'Berikan Akses Untuk Daftar Pembelian :',
                'App\Filament\Resources\PurchaseTransactionResource' => 'Berikan Akses Untuk Daftar Item Pembelian :',
            ],
            'Manajemen Projek' => [
                'App\Filament\Resources\ProjectResource' => 'Berikan Akses Untuk Daftar Proyek :',
                'App\Filament\Resources\ProjectTaskResource' => 'Berikan Akses Untuk Daftar Tugas Proyek :',
                'App\Filament\Resources\ProjectResorcessResource' => 'Berikan Akses Untuk Daftar Rencana dan Sumber Daya Proyek :',
                'App\Filament\Resources\ProjectMilestoneResource' => 'Berikan Akses Untuk Daftar Tahapan Proyek :',
                'App\Filament\Resources\ProjectMonitoringResource' => 'Berikan Akses Untuk Daftar Monitoring Proyek :',
            ],
        ];

        return collect($resourceGroups)
            ->map(function ($resources, $groupName) {
                return Forms\Components\Section::make($groupName)
                    // ->description(fn() => new HtmlString('<span class="text-sm font-medium text-gray-600 dark:text-gray-300">' . $groupName . '</span>'))
                    ->compact()
                    ->schema(
                        collect(FilamentShield::getResources())
                            ->filter(fn($entity) => array_key_exists($entity['fqcn'], $resources))
                            ->map(function ($entity) use ($resources) {
                                return Forms\Components\CheckboxList::make($entity['resource'])
                                    ->options([
                                        'view_' . $entity['resource'] => 'Lihat Data',
                                        'create_' . $entity['resource'] => 'Buat Data',
                                        'update_' . $entity['resource'] => 'Perbarui Data',
                                        'delete_' . $entity['resource'] => 'Hapus Data',
                                    ])
                                    ->columns(2)
                                    ->gridDirection('row')
                                    ->bulkToggleable()
                                    ->live()
                                    ->afterStateHydrated(function ($component, $state, $record) use ($entity) {
                                        if ($record) {
                                            $permissions = $record->permissions->pluck('name')->toArray();
                                            $selected = [];

                                            foreach ($component->getOptions() as $value => $label) {
                                                if (in_array($value, $permissions)) {
                                                    $selected[] = $value;
                                                }
                                            }

                                            $component->state($selected);
                                        }
                                    })
                                    ->dehydrated()
                                    ->columns(2)
                                    ->label($resources[$entity['fqcn']]);
                            })
                            ->toArray()
                    )
                    ->collapsible()
                    ->persistCollapsed()
                    ->columnSpan(1)
                    ->extraAttributes([
                        'class' => 'bg-white dark:bg-gray-800 shadow-lg rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-all duration-200'
                    ]);
            })
            ->values()
            ->toArray();
    }

    public static function getResourceTabBadgeCount(): ?int
    {
        return collect(FilamentShield::getResources())
            ->map(fn($resource) => count(static::getResourcePermissionOptions($resource)))
            ->sum();
    }

    public static function getResourcePermissionOptions(array $entity): array
    {
        return collect(Utils::getResourcePermissionPrefixes($entity['fqcn']))
            ->flatMap(function ($permission) use ($entity) {
                $name = $permission . '_' . $entity['resource'];
                $label = static::shield()->hasLocalizedPermissionLabels()
                    ? FilamentShield::getLocalizedResourcePermissionLabel($permission)
                    : $name;

                return [
                    $name => $label,
                ];
            })
            ->toArray();
    }

    // public static function setPermissionStateForRecordPermissions(Component $component, string $operation, array $permissions, ?Model $record): void
    // {

    //     if (in_array($operation, ['edit', 'view'])) {

    //         if (blank($record)) {
    //             return;
    //         }
    //         if ($component->isVisible() && count($permissions) > 0) {
    //             $component->state(
    //                 collect($permissions)
    //                     /** @phpstan-ignore-next-line */
    //                     ->filter(fn($value, $key) => $record->checkPermissionTo($key))
    //                     ->keys()
    //                     ->toArray()
    //             );
    //         }
    //     }
    // }
    public static function setPermissionStateForRecordPermissions(Component $component, string $operation, array $permissions, ?Model $record): void
    {
        if (in_array($operation, ['edit', 'view'])) {
            if ($component->isVisible() && count($permissions) > 0) {
                // Get all permissions including direct and inherited ones
                $permissionNames = collect($permissions)->keys()->toArray();
                $selectedPermissions = $record?->getAllPermissions()
                    ->whereIn('name', $permissionNames)
                    ->pluck('name')
                    ->toArray() ?? [];

                $component->state($selectedPermissions);
            }
        }
    }

    // public static function getPageOptions(): array
    // {
    //     return collect(FilamentShield::getPages())
    //         ->flatMap(fn($page) => [
    //             $page['permission'] => static::shield()->hasLocalizedPermissionLabels()
    //                 ? FilamentShield::getLocalizedPageLabel($page['class'])
    //                 : $page['permission'],
    //         ])
    //         ->toArray();
    // }

    public static function getPageOptions(): array
    {
        return collect(FilamentShield::getPages())
            ->flatMap(fn($page) => [
                $page['permission'] => match ($page['permission']) {
                    'page_Overview' => 'Berikan User Akses Halaman Dashboard Overview',
                    'page_Analytics' => 'Berikan User Akses Halaman Dashboard Analisis',
                    'page_Reports' => 'Berikan User Akses Halaman Semua Laporan',
                    'page_Wallet' => 'Berikan User Akses Halaman Dompet',
                    'page_Webchat' => 'Berikan User Akses Halaman Webchat',
                    'page_cctv' => 'Berikan User Akses Halaman CCTV',
                    'page_Map' => 'Berikan User Akses Halaman Maps User Location',
                    'page_Themes' => 'Berikan User Akses Halaman Tema Dashboard',

                    // cluster
                    'page_CustomerRelations' => 'Berikan User Akses Cluster Hubungan Pelanggan',
                    'page_CustomerSupport' => 'Berikan User Akses Cluster Dukungan Pelanggan',
                    'page_FinancialReporting' => 'Berikan User Akses Cluster Laporan Keuangan',
                    'page_Ledger' => 'Berikan User Akses Cluster Buku Besar',
                    'page_Procurement' => 'Berikan User Akses Cluster Pengadaan',
                    'page_ProjectPlanning' => 'Berikan User Akses Cluster Perencanaan Proyek',
                    'page_Sales' => 'Berikan User Akses Cluster Penjualan',
                    default => $page['permission'],
                }
            ])
            ->toArray();
    }

    // public static function getWidgetOptions(): array
    // {
    //     return collect(FilamentShield::getWidgets())
    //         ->flatMap(fn($widget) => [
    //             $widget['permission'] => static::shield()->hasLocalizedPermissionLabels()
    //                 ? FilamentShield::getLocalizedWidgetLabel($widget['class'])
    //                 : $widget['permission'],
    //         ])
    //         ->toArray();
    // }

    public static function getWidgetOptions(): array
    {
        $dashboardWidgets = [
            'widget_CustomerDoughnutChart' => 'Grafik Donat Pelanggan (Dashboard)',
            'widget_CustomerLineChart' => 'Grafik Garis Pelanggan (Dashboard)',
            'widget_CustomerStatsOverview' => 'Statistik Pelanggan (Dashboard)',
            'widget_FinancialLineChart' => 'Grafik Garis Keuangan (Dashboard)',
            'widget_FinancialPieChart' => 'Grafik Pie Keuangan (Dashboard)',
            'widget_FinancialStatsOverview' => 'Statistik Keuangan (Dashboard)',
            'widget_HrBarChart' => 'Grafik Batang HR (Dashboard)',
            'widget_HrDoughnutChart' => 'Grafik Donat HR (Dashboard)',
            'widget_HrStatsOverview' => 'Statistik HR (Dashboard)',
            'widget_InventoryBarChart' => 'Grafik Batang Inventaris (Dashboard)',
            'widget_Inventory' => 'Grafik Tabel Inventaris (Dashboard)',
            'widget_InventoryFulfillmentChart' => 'Grafik Pemenuhan Inventaris (Dashboard)',
            'widget_InventoryLineChart' => 'Grafik Garis Inventaris (Dashboard)',
            'widget_ProjectBarChart' => 'Grafik Batang Proyek (Dashboard)',
            'widget_ProjectPieChart' => 'Grafik Pie Proyek (Dashboard)',
            'widget_ProjectStatsOverview' => 'Statistik Proyek (Dashboard)',
            'widget_ProjectTableWidget' => 'Grafik Tabel Proyek (Dashboard)',
            'widget_SalesBarChart' => 'Grafik Batang Penjualan (Dashboard)',
            'widget_SalesDoughnutChart' => 'Grafik Donat Penjualan (Dashboard)',
            'widget_SalesStatsOverview' => 'Statistik Penjualan (Dashboard)',
            'widget_SalesTableWidget' => 'Grafik Tabel Penjualan (Dashboard)',
            'widget_SupplierPendingPaymentTableWidget' => 'Grafik Tabel Pembayaran Tertunda Pemasok (Dashboard)',
            'widget_SupplierRadarChart' => 'Grafik Radar Pemasok (Dashboard)',
            'widget_SupplierTableWidget' => 'Grafik Tabel Pemasok (Dashboard)',
            'widget_TopInventoryItemsTableWidget' => 'Grafik Tabel Item Inventaris Teratas (Dashboard)',

        ];

        $resourceWidgets = [
            'widget_JournalEntryStats' => 'Statistik Jurnal',
            'widget_BalanceSheetStats' => 'Statistik Neraca',
            'widget_CustomerStats' => 'Statistik Pelanggan',
            'widget_InventoryStats' => 'Statistik Inventaris',
            'widget_ProjectStats' => 'Statistik Proyek',
        ];

        return collect(FilamentShield::getWidgets())
            ->flatMap(fn($widget) => [
                $widget['permission'] => $dashboardWidgets[$widget['permission']] ?? $resourceWidgets[$widget['permission']] ?? $widget['permission']
            ])
            ->toArray();
    }

    public static function getCustomPermissionOptions(): ?array
    {
        return FilamentShield::getCustomPermissions()
            ->mapWithKeys(fn($customPermission) => [
                $customPermission => static::shield()->hasLocalizedPermissionLabels() ? str($customPermission)->headline()->toString() : $customPermission,
            ])
            ->toArray();
    }

    public static function getTabFormComponentForResources(): Component
    {
        return static::shield()->hasSimpleResourcePermissionView()
            ? static::getTabFormComponentForSimpleResourcePermissionsView()
            : Forms\Components\Tabs\Tab::make('resources')
            ->label(__('filament-shield::filament-shield.resources'))
            ->visible(fn(): bool => (bool) Utils::isResourceEntityEnabled())
            // ->badge(static::getResourceTabBadgeCount())
            ->schema([
                Forms\Components\Grid::make()
                    ->schema(static::getResourceEntitiesSchema())
                    ->columns(static::shield()->getGridColumns()),
            ]);
    }

    public static function getCheckBoxListComponentForResource(array $entity): Component
    {
        $permissionsArray = static::getResourcePermissionOptions($entity);

        return static::getCheckboxListFormComponent($entity['resource'], $permissionsArray, false);
    }

    public static function getTabFormComponentForPage(): Component
    {
        $options = static::getPageOptions();
        $count = count($options);

        return Forms\Components\Tabs\Tab::make('pages')
            ->label(__('filament-shield::filament-shield.pages'))
            ->visible(fn(): bool => (bool) Utils::isPageEntityEnabled() && $count > 0)
            // ->badge($count)
            ->schema([
                static::getCheckboxListFormComponent('pages_tab', $options),
            ]);
    }

    public static function getTabFormComponentForWidget(): Component
    {
        $options = static::getWidgetOptions();
        $count = count($options);

        return Forms\Components\Tabs\Tab::make('widgets')
            ->label(__('filament-shield::filament-shield.widgets'))
            ->visible(fn(): bool => (bool) Utils::isWidgetEntityEnabled() && $count > 0)
            // ->badge($count)
            ->schema([
                static::getCheckboxListFormComponent('widgets_tab', $options),
            ]);
    }

    public static function getTabFormComponentForCustomPermissions(): Component
    {
        $options = static::getCustomPermissionOptions();
        $count = count($options);

        return Forms\Components\Tabs\Tab::make('custom')
            ->label(__('filament-shield::filament-shield.custom'))
            ->visible(fn(): bool => (bool) Utils::isCustomPermissionEntityEnabled() && $count > 0)
            ->badge($count)
            ->schema([
                static::getCheckboxListFormComponent('custom_permissions', $options),
            ]);
    }

    public static function getTabFormComponentForSimpleResourcePermissionsView(): Component
    {
        $options = FilamentShield::getAllResourcePermissions();
        $count = count($options);

        return Forms\Components\Tabs\Tab::make('resources')
            ->label(__('filament-shield::filament-shield.resources'))
            ->visible(fn(): bool => (bool) Utils::isResourceEntityEnabled() && $count > 0)
            ->badge($count)
            ->schema([
                static::getCheckboxListFormComponent('resources_tab', $options),
            ]);
    }

    public static function getCheckboxListFormComponent(string $name, array $options, bool $searchable = true): Component
    {
        return Forms\Components\CheckboxList::make($name)
            ->label('')
            ->options(fn(): array => $options)
            ->searchable($searchable)
            ->afterStateHydrated(
                fn(Component $component, string $operation, ?Model $record) => static::setPermissionStateForRecordPermissions(
                    component: $component,
                    operation: $operation,
                    permissions: $options,
                    record: $record
                )
            )
            ->dehydrated(fn($state) => ! blank($state))
            ->bulkToggleable()
            ->gridDirection('row')
            ->columns(static::shield()->getCheckboxListColumns())
            ->columnSpan(static::shield()->getCheckboxListColumnSpan());
    }

    public static function shield(): FilamentShieldPlugin
    {
        return FilamentShieldPlugin::get();
    }
}

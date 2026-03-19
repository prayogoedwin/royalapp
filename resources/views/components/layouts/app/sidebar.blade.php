            <aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
                class="bg-sidebar text-sidebar-foreground border-r border-gray-200 dark:border-gray-700 sidebar-transition overflow-hidden">
                <!-- Sidebar Content -->
                <div class="h-full flex flex-col">
                    <!-- Sidebar Menu -->
                    <nav class="flex-1 overflow-y-auto custom-scrollbar py-4">
                        <ul class="space-y-1 px-2">
                            <x-layouts.sidebar-link href="{{ route('dashboard') }}" icon='fas-house'
                                :active="request()->routeIs('dashboard*')">Dashboard</x-layouts.sidebar-link>

                            @php
                                $hasUserManagement =
                                    auth()->user()->hasPermission('view-users') ||
                                    auth()->user()->hasPermission('view-roles') ||
                                    auth()->user()->hasPermission('view-permissions');
                            @endphp

                            @if($hasUserManagement)
                                <x-layouts.sidebar-two-level-link-parent title="User Management" icon="fas-users"
                                    :active="request()->routeIs('users*') || request()->routeIs('roles*') || request()->routeIs('permissions*')">
                                    @if(auth()->user()->hasPermission('view-users'))
                                        <x-layouts.sidebar-two-level-link href="{{ route('users.index') }}" icon='fas-user'
                                            :active="request()->routeIs('users*')">Users</x-layouts.sidebar-two-level-link>
                                    @endif
                                    @if(auth()->user()->hasPermission('view-roles'))
                                        <x-layouts.sidebar-two-level-link href="{{ route('roles.index') }}" icon='fas-shield'
                                            :active="request()->routeIs('roles*')">Roles</x-layouts.sidebar-two-level-link>
                                    @endif
                                    @if(auth()->user()->hasPermission('view-permissions'))
                                        <x-layouts.sidebar-two-level-link href="{{ route('permissions.index') }}" icon='fas-key'
                                            :active="request()->routeIs('permissions*')">Permissions</x-layouts.sidebar-two-level-link>
                                    @endif
                                </x-layouts.sidebar-two-level-link-parent>
                            @endif

                            @if(auth()->user()->hasPermission('view-orders'))
                            <x-layouts.sidebar-link href="{{ route('orders.index') }}" icon='fas-file-invoice'
                                :active="request()->routeIs('orders*')">Orders</x-layouts.sidebar-link>
                            @endif

                            @if(auth()->user()->hasPermission('view-orders'))
                            <x-layouts.sidebar-link href="{{ route('order-vehicle-issues.index') }}" icon='fas-wrench'
                                :active="request()->routeIs('order-vehicle-issues*')">Vehicle Issues</x-layouts.sidebar-link>
                            @endif

                            @if(auth()->user()->hasPermission('view-employees'))
                            <x-layouts.sidebar-link href="{{ route('employees.index') }}" icon='fas-id-card'
                                :active="request()->routeIs('employees*')">Employees</x-layouts.sidebar-link>
                            @endif

                            {{-- Presensi menu khusus pegawai (buka presensi milik sendiri) --}}
                            @if(auth()->user()->hasPermission('view-presensi'))
                            <x-layouts.sidebar-link href="{{ route('presensi.my') }}" icon='fas-calendar-check'
                                :active="request()->routeIs('presensi.my')">Presensi</x-layouts.sidebar-link>
                            @endif

                            {{-- Absensi hari ini untuk semua pegawai (admin) --}}
                            @if(auth()->user()->hasPermission('view-presensi-all'))
                                <x-layouts.sidebar-link href="{{ route('absensi.today.all') }}" icon='fas-calendar-day'
                                    :active="request()->routeIs('absensi.today.all')">Absensi All</x-layouts.sidebar-link>
                            @endif

                            @php
                                $hasMasterData =
                                    auth()->user()->hasPermission('view-positions') ||
                                    auth()->user()->hasPermission('view-divisions') ||
                                    auth()->user()->hasPermission('view-employee-types') ||
                                    auth()->user()->hasPermission('view-units') ||
                                    auth()->user()->hasPermission('view-pools');
                            @endphp

                            @if($hasMasterData)
                                <x-layouts.sidebar-two-level-link-parent title="Master Data" icon="fas-database"
                                    :active="request()->routeIs('positions*') || request()->routeIs('divisions*') || request()->routeIs('employee-types*') || request()->routeIs('units*') || request()->routeIs('pools*')">
                                    @if(auth()->user()->hasPermission('view-positions'))
                                        <x-layouts.sidebar-two-level-link href="{{ route('positions.index') }}" icon='fas-briefcase'
                                            :active="request()->routeIs('positions*')">Positions</x-layouts.sidebar-two-level-link>
                                    @endif
                                    @if(auth()->user()->hasPermission('view-divisions'))
                                        <x-layouts.sidebar-two-level-link href="{{ route('divisions.index') }}" icon='fas-building'
                                            :active="request()->routeIs('divisions*')">Divisions</x-layouts.sidebar-two-level-link>
                                    @endif
                                    @if(auth()->user()->hasPermission('view-employee-types'))
                                        <x-layouts.sidebar-two-level-link href="{{ route('employee-types.index') }}" icon='fas-tags'
                                            :active="request()->routeIs('employee-types*')">Employee Types</x-layouts.sidebar-two-level-link>
                                    @endif
                                    @if(auth()->user()->hasPermission('view-units'))
                                        <x-layouts.sidebar-two-level-link href="{{ route('units.index') }}" icon='fas-truck'
                                            :active="request()->routeIs('units*')">Units</x-layouts.sidebar-two-level-link>
                                    @endif
                                    @if(auth()->user()->hasPermission('view-pools'))
                                        <x-layouts.sidebar-two-level-link href="{{ route('pools.index') }}" icon='fas-map-marker-alt'
                                            :active="request()->routeIs('pools*')">Pools</x-layouts.sidebar-two-level-link>
                                    @endif
                                </x-layouts.sidebar-two-level-link-parent>
                            @endif

                            <x-layouts.sidebar-two-level-link-parent title="Example two level" icon="fas-house"
                                :active="request()->routeIs('two-level*')">
                                <x-layouts.sidebar-two-level-link href="#" icon='fas-house'
                                    :active="request()->routeIs('two-level*')">Child</x-layouts.sidebar-two-level-link>
                            </x-layouts.sidebar-two-level-link-parent>

                            <x-layouts.sidebar-three-level-parent title="Example three level" icon="fas-house"
                                :active="request()->routeIs('three-level*')">
                                <x-layouts.sidebar-two-level-link href="#" icon='fas-house'
                                    :active="request()->routeIs('three-level*')">Single Link</x-layouts.sidebar-two-level-link>

                                <x-layouts.sidebar-three-level-parent title="Third Level" icon="fas-house"
                                    :active="request()->routeIs('three-level*')">
                                    <x-layouts.sidebar-three-level-link href="#" :active="request()->routeIs('three-level*')">
                                        Third Level Link
                                    </x-layouts.sidebar-three-level-link>
                                </x-layouts.sidebar-three-level-parent>
                            </x-layouts.sidebar-three-level-parent>
                        </ul>
                    </nav>
                </div>
            </aside>

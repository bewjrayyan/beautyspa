<aside class="w-64 bg-gray-900 h-screen border-r border-gray-800 flex-shrink-0">
    <div class="flex h-16 items-center justify-between px-6">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-white font-medium hover:text-primary transition-colors">
            <i class="fa fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <button class="md:hidden p-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition-colors" aria-label="Toggle menu">
            <i class="fa fa-bars text-white"></i>
        </button>
    </div>

    <nav class="px-2 pt-2">
        <ul class="space-y-1">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-900/20 transition-colors font-medium <?php echo request()->is('admin*') ? 'bg-gray-900 text-white' : ''; ?>">
                    <i class="fa fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-900/20 transition-colors font-medium">
                    <i class="fa fa-users"></i>
                    <span>Users</span>
                </a>
            </li>

            <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-900/20 transition-colors font-medium">
                    <i class="fa fa-box"></i>
                    <span>Products</span>
                </a>
            </li>

            <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-900/20 transition-colors font-medium">
                    <i class="fa fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>

            <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-900/20 transition-colors font-medium">
                    <i class="fa fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>

            <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-900/20 transition-colors font-medium">
                    <i class="fa fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="px-2 border-t border-gray-800 pt-6">
        <div class="px-3 py-2 text-xs text-gray-400">
            FleetCart v3.11
        </div>
    </div>
</aside>

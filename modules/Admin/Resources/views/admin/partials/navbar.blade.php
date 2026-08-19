<nav class="border-b border-gray-100 bg-white shadow-sm sticky top-0 z-20">
    <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-primary font-medium hover:text-blue-600 transition-colors">
                <i class="fa fa-home"></i>
                <span>FleetCart Admin</span>
            </a>
        </div>

        <div class="hidden md:flex items-center gap-6">
            <a href="#" class="text-gray-500 hover:text-primary transition-colors" aria-label="Notifications">
                <i class="fa fa-bell"></i>
                <span class="hidden md:inline-block ml-1">3</span>
            </a>
            <a href="#" class="text-gray-500 hover:text-primary transition-colors" aria-label="Messages">
                <i class="fa fa-envelope"></i>
            </a>
            <a href="#" class="text-gray-500 hover:text-primary transition-colors" aria-label="Settings">
                <i class="fa fa-cog"></i>
            </a>
        </div>
    </div>
</nav>

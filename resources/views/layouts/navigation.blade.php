<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @auth
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('common.Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.bookings.index')" :active="request()->routeIs('admin.bookings.*')">
                        {{ __('common.Manage Bookings') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.properties.index')" :active="request()->routeIs('admin.properties.*')">
                        {{ __('common.Manage Properties') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.locations.index')" :active="request()->routeIs('admin.locations.*')">
                        {{ __('common.Manage Locations') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.houses.index')" :active="request()->routeIs('admin.houses.*')">
                        {{ __('common.Manage Houses') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.rooms.index')" :active="request()->routeIs('admin.rooms.*')">
                        {{ __('common.Manage Rooms') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')">
                        {{ __('common.Settings') }}
                    </x-nav-link>
                    @endauth
                    <x-nav-link :href="route('booking.index')" :active="request()->routeIs('booking.*')">
                        {{ __('common.View Rooms') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Visit Us Link -->
                <a href="{{ route('booking-flow.home') }}" 
                   class="inline-flex items-center px-4 py-2 mr-4 bg-white text-blue-600 rounded-lg font-semibold hover:bg-blue-50 transition-colors shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    {{ __('dashboard.visit_us') }}
                </a>
                
                <!-- Language Toggle -->
                <div class="flex items-center mr-4 bg-gray-100 rounded-lg p-1">
                    <form method="POST" action="{{ route('set-locale') }}" class="inline">
                        @csrf
                        <input type="hidden" name="locale" value="en">
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                        <button type="submit" 
                                class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ app()->getLocale() === 'en' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                            EN
                        </button>
                    </form>
                    <form method="POST" action="{{ route('set-locale') }}" class="inline">
                        @csrf
                        <input type="hidden" name="locale" value="de">
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                        <button type="submit" 
                                class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ app()->getLocale() === 'de' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                            DE
                        </button>
                    </form>
                </div>
                
                @auth
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('common.Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('common.Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                @else
                <div class="flex items-center">
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900 mr-4">Login</a>
                    <a href="{{ route('register') }}" class="text-sm bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Register</a>
                </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('common.Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.bookings.index')" :active="request()->routeIs('admin.bookings.*')">
                {{ __('common.Manage Bookings') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.properties.index')" :active="request()->routeIs('admin.properties.*')">
                {{ __('common.Manage Properties') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.locations.index')" :active="request()->routeIs('admin.locations.*')">
                {{ __('common.Manage Locations') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.houses.index')" :active="request()->routeIs('admin.houses.*')">
                {{ __('common.Manage Houses') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.rooms.index')" :active="request()->routeIs('admin.rooms.*')">
                {{ __('common.Manage Rooms') }}
            </x-responsive-nav-link>
            @endauth
            <x-responsive-nav-link :href="route('booking.index')" :active="request()->routeIs('booking.*')">
                {{ __('common.View Rooms') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <!-- Language Toggle for Mobile -->
            <div class="px-4 mb-4">
                <div class="flex items-center bg-gray-100 rounded-lg p-1">
                    <form method="POST" action="{{ route('set-locale') }}" class="inline flex-1">
                        @csrf
                        <input type="hidden" name="locale" value="en">
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                        <button type="submit" 
                                class="w-full px-3 py-2 text-sm font-medium rounded-md transition-colors {{ app()->getLocale() === 'en' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                            English
                        </button>
                    </form>
                    <form method="POST" action="{{ route('set-locale') }}" class="inline flex-1">
                        @csrf
                        <input type="hidden" name="locale" value="de">
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                        <button type="submit" 
                                class="w-full px-3 py-2 text-sm font-medium rounded-md transition-colors {{ app()->getLocale() === 'de' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                            Deutsch
                        </button>
                    </form>
                </div>
            </div>
            
            @auth
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('common.Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('common.Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
            @else
            <div class="px-4 space-y-2">
                <a href="{{ route('login') }}" class="block text-sm text-gray-700 hover:text-gray-900">Login</a>
                <a href="{{ route('register') }}" class="block text-sm bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-center">Register</a>
            </div>
            @endauth
        </div>
    </div>
</nav>

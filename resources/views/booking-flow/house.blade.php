<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('booking_flow.living_in') }} {{ $location->name }} – {{ config('app.name', 'Ma-Room') }}</title>
    
    <!-- Favicon - Logo -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }
        .house-gallery-item {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .house-gallery-item:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        .amenity-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .amenity-item::before {
            content: "•";
            color: #4caf50;
            font-weight: bold;
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }
        /* Modal Styles */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            overflow: auto;
        }
        .image-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
            margin: auto;
        }
        .modal-image {
            width: 100%;
            height: auto;
            max-height: 90vh;
            object-fit: contain;
        }
        .close-modal {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            z-index: 1001;
        }
        .close-modal:hover {
            color: #ccc;
        }
        .modal-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #fff;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
            padding: 10px 15px;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 5px;
            z-index: 1001;
        }
        .modal-nav:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }
        .modal-nav.prev {
            left: 20px;
        }
        .modal-nav.next {
            right: 20px;
        }
        .modal-counter {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: #fff;
            font-size: 18px;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 10px 20px;
            border-radius: 5px;
        }
        /* Swiper Styles */
        .house-swiper {
            width: 100%;
            height: 600px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        .house-swiper .swiper-slide {
            text-align: center;
            font-size: 18px;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }
        .house-swiper .swiper-slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .house-swiper .swiper-button-next,
        .house-swiper .swiper-button-prev {
            color: #fff;
            background: rgba(0, 0, 0, 0.5);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        .house-swiper .swiper-button-next:hover,
        .house-swiper .swiper-button-prev:hover {
            background: rgba(0, 0, 0, 0.8);
        }
        .house-swiper .swiper-button-next:after,
        .house-swiper .swiper-button-prev:after {
            font-size: 20px;
            font-weight: bold;
        }
        .house-swiper .swiper-pagination-bullet {
            background: #fff;
            opacity: 0.5;
            width: 12px;
            height: 12px;
        }
        .house-swiper .swiper-pagination-bullet-active {
            opacity: 1;
            background: #2563eb;
        }
        @media (max-width: 768px) {
            .house-swiper {
                height: 400px;
            }
        }
    </style>
</head>
<body class="font-sans antialiased bg-white">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 py-4 px-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="{{ route('booking-flow.home') }}" class="text-gray-700 hover:text-gray-900">
                <span class="font-semibold">{{ __('booking_flow.home') }}</span>
            </a>
            <button class="text-gray-700 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </header>

    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Main Title -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                    {{ __('booking_flow.living_in') }} {{ $location->name }}
                </h1>
            </div>

            <!-- Description Section -->
            <div class="mb-12 max-w-4xl mx-auto">
                <div class="prose prose-lg max-w-none">
                    <p class="text-gray-700 leading-relaxed mb-6">
                        {{ __('booking_flow.at_haus_offer', ['location' => $location->name, 'count' => $houses->count(), 'house' => $houses->count() === 1 ? __('booking_flow.house_singular') : __('booking_flow.house_plural')]) }}
                    </p>
                </div>
            </div>

            <!-- Room Amenities Section -->
            <div class="mb-12 max-w-4xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('booking_flow.amenities_comfort') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    @php
                        // Get amenities from first house, or use default
                        $amenitiesText = $houses->first()->amenities_text ?? null;
                        $defaultAmenities = [
                            __('booking_flow.amenity_large_beds'),
                            __('booking_flow.amenity_fast_wifi'),
                            __('booking_flow.amenity_weekly_cleaning'),
                            __('booking_flow.amenity_smart_tv'),
                            __('booking_flow.amenity_prices_included'),
                            __('booking_flow.amenity_washer_dryer'),
                            __('booking_flow.amenity_central_location'),
                            __('booking_flow.amenity_fully_equipped_kitchen'),
                            __('booking_flow.amenity_parking'),
                        ];
                        
                        if ($amenitiesText) {
                            $amenities = array_filter(array_map('trim', explode("\n", $amenitiesText)));
                            // Remove emojis from amenities
                            $amenities = array_map(function($item) {
                                return preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $item);
                            }, $amenities);
                        } else {
                            $amenities = $defaultAmenities;
                        }
                    @endphp
                    @foreach($amenities as $amenity)
                        <div class="amenity-item">
                            <span class="text-gray-700">{{ $amenity }}</span>
                        </div>
                    @endforeach
                </div>

                @if(strtolower($location->name) === 'fürstenwalde')
                <!-- Fürstenwalde Specific Content -->
                <div class="mb-8 space-y-4">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <p class="text-gray-700 leading-relaxed">
                            <strong>{{ __('booking_flow.furstenwalde_spree_title') }}</strong> {{ __('booking_flow.furstenwalde_spree_description') }}
                        </p>
                    </div>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                        <p class="text-gray-700 leading-relaxed">
                            <strong>{{ __('booking_flow.furstenwalde_nature_title') }}</strong> {{ __('booking_flow.furstenwalde_nature_description') }}
                        </p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Button Above Pictures -->
            @if($firstHouseWithRooms)
            <div class="text-center mb-8">
                <a href="{{ route('booking-flow.search', ['location' => $location->id, 'house' => $firstHouseWithRooms->id]) }}" 
                   class="inline-flex items-center px-8 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold text-lg shadow-lg hover:shadow-xl">
                    {{ $firstHouseWithRooms->button_text ?? __('booking_flow.view_available_rooms') }}
                </a>
            </div>
            @endif

            <!-- Houses Image Slider -->
            @php
                $allImages = [];
                foreach($houses as $house) {
                    if ($house->images && $house->images->count() > 0) {
                        foreach($house->images as $image) {
                            $allImages[] = [
                                'id' => $image->id,
                                'path' => asset('storage/' . $image->path),
                                'house_id' => $house->id,
                                'house_name' => $house->name
                            ];
                        }
                    } elseif ($house->image) {
                        $allImages[] = [
                            'id' => 'single',
                            'path' => asset('storage/' . $house->image),
                            'house_id' => $house->id,
                            'house_name' => $house->name
                        ];
                    }
                }
            @endphp
            
            @if(count($allImages) > 0)
            <div class="mb-12">
                <div class="swiper house-swiper">
                    <div class="swiper-wrapper">
                        @foreach($allImages as $index => $img)
                        <div class="swiper-slide" onclick="openImageModalFromSlider({{ $img['house_id'] }}, '{{ $img['id'] }}', {{ $index }})">
                            <img src="{{ $img['path'] }}" alt="{{ $img['house_name'] }}" loading="lazy">
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>
            @endif

            <!-- Image Modal -->
            <div id="imageModal" class="image-modal">
                <span class="close-modal" onclick="closeImageModal()">&times;</span>
                <span class="modal-nav prev" onclick="changeImage(-1)">&#10094;</span>
                <span class="modal-nav next" onclick="changeImage(1)">&#10095;</span>
                <div class="modal-content">
                    <img id="modalImage" class="modal-image" src="" alt="House Image">
                    <div class="modal-counter">
                        <span id="imageCounter"></span>
                    </div>
                </div>
            </div>

            <!-- Guest Favorite Badge -->
            <div class="max-w-4xl mx-auto text-center mb-12">
                <div class="inline-block bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="text-3xl font-bold text-yellow-600 mb-2">4.65</div>
                    <div class="text-lg font-semibold text-gray-900 mb-2">
                        <strong>{{ __('booking_flow.guest_favorite') }}</strong>
                    </div>
                    <p class="text-sm text-gray-600">
                        {{ __('booking_flow.guest_favorite_description') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-100 border-t border-gray-200 py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center text-gray-600 text-sm">
                <p>{{ __('booking_flow.copyright') }}</p>
                <div class="mt-4 space-x-4">
                    <a href="#" class="hover:text-gray-900">{{ __('booking_flow.imprint') }}</a>
                    <span>|</span>
                    <a href="#" class="hover:text-gray-900">{{ __('booking_flow.privacy_policy') }}</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <script>
        // Initialize Swiper
        const houseSwiper = new Swiper('.house-swiper', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            keyboard: {
                enabled: true,
            },
        });

        // Store all house images data
        const houseImages = @json($houseImagesData);

        let currentImageIndex = 0;

        function openImageModal(houseId, imageId) {
            // Find the index of the clicked image
            const index = houseImages.findIndex(img => 
                img.house_id === houseId && (img.id === imageId || (imageId === 'single' && img.id === 'single'))
            );
            
            if (index !== -1) {
                currentImageIndex = index;
                updateModalImage();
                document.getElementById('imageModal').classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function openImageModalFromSlider(houseId, imageId, sliderIndex) {
            // Find the index in houseImages array
            const index = houseImages.findIndex(img => 
                img.house_id === houseId && (String(img.id) === String(imageId) || (imageId === 'single' && img.id === 'single'))
            );
            
            if (index !== -1) {
                currentImageIndex = index;
                updateModalImage();
                document.getElementById('imageModal').classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function changeImage(direction) {
            currentImageIndex += direction;
            
            if (currentImageIndex < 0) {
                currentImageIndex = houseImages.length - 1;
            } else if (currentImageIndex >= houseImages.length) {
                currentImageIndex = 0;
            }
            
            updateModalImage();
        }

        function updateModalImage() {
            if (houseImages.length === 0) return;
            
            const image = houseImages[currentImageIndex];
            document.getElementById('modalImage').src = image.path;
            document.getElementById('modalImage').alt = image.house_name;
            document.getElementById('imageCounter').textContent = `${currentImageIndex + 1} / ${houseImages.length}`;
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            } else if (e.key === 'ArrowLeft') {
                changeImage(-1);
            } else if (e.key === 'ArrowRight') {
                changeImage(1);
            }
        });

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    </script>
</body>
</html>

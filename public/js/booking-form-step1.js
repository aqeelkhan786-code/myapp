(function () {
    'use strict';

    var configEl = document.getElementById('booking-form-config');
    if (!configEl || !configEl.textContent) return;
    var config = JSON.parse(configEl.textContent);
    var roomsData = config.roomsData || [];
    var isLongTermRental = !!config.isLongTermRental;
    var translations = config.translations || {};

    function run() {
        var startAtInput = document.getElementById('start_at');
        if (startAtInput && startAtInput.value) {
            var startDate = new Date(startAtInput.value);
            var tenancyFrom = document.getElementById('tenancy-from');
            if (tenancyFrom) {
                tenancyFrom.textContent = String(startDate.getDate()).padStart(2, '0') + '.' +
                    String(startDate.getMonth() + 1).padStart(2, '0') + '.' +
                    startDate.getFullYear();
            }
        }

        var roomSelect = document.getElementById('room_id');
        if (roomSelect) {
            var selectedRoomId = roomSelect.value;
            var selectedRoom = roomsData.find(function (r) { return r.id == selectedRoomId; });
            if (selectedRoom) {
                var selectedRoomNameEl = document.getElementById('selected-room-name');
                var roomAddressEl = document.getElementById('room-address');
                var rentPerNightEl = document.getElementById('rent-per-night');
                var urlParams = new URLSearchParams(window.location.search);
                var checkOut = urlParams.get('check_out');
                var isLongTerm = !checkOut || checkOut === '';
                if (selectedRoomNameEl) selectedRoomNameEl.textContent = selectedRoom.name;
                if (roomAddressEl) roomAddressEl.value = selectedRoom.address;
                if (rentPerNightEl) {
                    var price = isLongTerm ? (selectedRoom.monthly_price || 700) : (selectedRoom.base_price || 0);
                    rentPerNightEl.textContent = '€' + parseFloat(price).toFixed(2);
                }
            }
        }

        var firstNameInput = document.getElementById('guest_first_name');
        var lastNameInput = document.getElementById('guest_last_name');
        var renterFullName = document.getElementById('renter-full-name');
        function updateRenterFullName() {
            var first = firstNameInput ? firstNameInput.value || '' : '';
            var last = lastNameInput ? lastNameInput.value || '' : '';
            var full = (first + ' ' + last).trim();
            if (renterFullName) renterFullName.textContent = full;
        }
        if (firstNameInput) {
            firstNameInput.addEventListener('input', updateRenterFullName);
            firstNameInput.addEventListener('change', updateRenterFullName);
        }
        if (lastNameInput) {
            lastNameInput.addEventListener('input', updateRenterFullName);
            lastNameInput.addEventListener('change', updateRenterFullName);
        }
        updateRenterFullName();

        if (isLongTermRental) {
            var signaturePad = null;
            function waitForSignaturePad(cb, maxAttempts) {
                maxAttempts = maxAttempts || 50;
                var attempts = 0;
                var iv = setInterval(function () {
                    attempts++;
                    if (typeof window.SignaturePad !== 'undefined') {
                        clearInterval(iv);
                        cb();
                    } else if (attempts >= maxAttempts) {
                        clearInterval(iv);
                    }
                }, 100);
            }
            function initSignaturePad() {
                var canvas = document.getElementById('signature-pad');
                if (!canvas) return;
                var w = Math.max(parseInt(canvas.getAttribute('width'), 10) || canvas.offsetWidth || 600, 300);
                var h = Math.max(parseInt(canvas.getAttribute('height'), 10) || canvas.offsetHeight || 200, 150);
                canvas.width = w;
                canvas.height = h;
                try {
                    signaturePad = new window.SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: 'rgb(0, 0, 0)',
                        minWidth: 1,
                        maxWidth: 3,
                        throttle: 8,
                        velocityFilterWeight: 0.7
                    });
                    window.signaturePad = signaturePad;
                    var clearBtn = document.getElementById('clear-signature');
                    if (clearBtn) {
                        clearBtn.addEventListener('click', function () {
                            if (signaturePad) signaturePad.clear();
                        });
                    }
                    canvas.style.pointerEvents = 'auto';
                    canvas.setAttribute('tabindex', '0');
                } catch (e) {
                    window.alert(translations.signaturePadError || 'Error initializing signature pad.');
                }
            }
            function startInit() {
                waitForSignaturePad(function () {
                    requestAnimationFrame(initSignaturePad);
                });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', startInit);
            } else {
                startInit();
            }
        }

        var form = document.getElementById('step1-form');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (isLongTermRental) {
                    var pad = window.signaturePad || null;
                    if (!pad) {
                        e.preventDefault();
                        window.alert(translations.signaturePadNotInitialized || 'Signature pad not initialized.');
                        return false;
                    }
                    if (pad.isEmpty()) {
                        e.preventDefault();
                        window.alert(translations.pleaseProvideSignature || 'Please provide your signature.');
                        return false;
                    }
                    try {
                        var data = pad.toDataURL('image/png');
                        var input = document.getElementById('signature-data');
                        if (input) {
                            input.value = data;
                        } else {
                            e.preventDefault();
                            window.alert(translations.signatureInputNotFound || 'Signature input not found.');
                            return false;
                        }
                    } catch (err) {
                        e.preventDefault();
                        window.alert(translations.errorCapturingSignature || 'Error capturing signature.');
                        return false;
                    }
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();

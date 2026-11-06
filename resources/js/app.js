document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.querySelector('[data-menu-button]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');

    menuButton?.addEventListener('click', () => {
        if (!mobileMenu) {
            return;
        }

        const isOpen = !mobileMenu.classList.toggle('hidden');
        menuButton.setAttribute('aria-expanded', String(isOpen));
        menuButton.setAttribute('aria-label', isOpen ? 'Đóng menu' : 'Mở menu');
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mobileMenu && !mobileMenu.classList.contains('hidden')) {
            mobileMenu.classList.add('hidden');
            menuButton?.setAttribute('aria-expanded', 'false');
            menuButton?.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (mobileMenu && !mobileMenu.classList.contains('hidden') && !event.target.closest('[data-menu-button], [data-mobile-menu]')) {
            mobileMenu.classList.add('hidden');
            menuButton?.setAttribute('aria-expanded', 'false');
        }
    });

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const heroCarousel = document.querySelector('[data-hero-carousel]');

    if (heroCarousel) {
        const heroSlides = (heroCarousel.dataset.heroSlides ?? '').split('|').filter(Boolean);
        const heroImage = heroCarousel.querySelector('[data-hero-image]');
        const heroIndicators = [...heroCarousel.querySelectorAll('[data-hero-indicator]')];

        if (heroImage && heroSlides.length > 1) {
            let activeSlide = 0;
            let transitionTimeout;
            let rotationInterval;

            const showHeroSlide = (slideIndex) => {
                const nextSlide = (slideIndex + heroSlides.length) % heroSlides.length;

                const updateHeroSlide = () => {
                    activeSlide = nextSlide;
                    heroImage.style.setProperty('--hero-slide-image', `url("${heroSlides[activeSlide]}")`);
                    heroImage.classList.remove('opacity-0');

                    heroIndicators.forEach((indicator, indicatorIndex) => {
                        const isActive = indicatorIndex === activeSlide;

                        indicator.classList.toggle('w-5', isActive);
                        indicator.classList.toggle('w-2', !isActive);
                        indicator.classList.toggle('bg-white', isActive);
                        indicator.classList.toggle('bg-white/45', !isActive);
                        indicator.setAttribute('aria-pressed', String(isActive));
                    });
                };

                if (reducedMotion) {
                    updateHeroSlide();

                    return;
                }

                heroImage.classList.add('opacity-0');
                window.clearTimeout(transitionTimeout);
                transitionTimeout = window.setTimeout(updateHeroSlide, 250);
            };

            const restartRotation = () => {
                window.clearInterval(rotationInterval);
                rotationInterval = window.setInterval(() => {
                    showHeroSlide((activeSlide + 1) % heroSlides.length);
                }, 3000);
            };

            heroIndicators.forEach((indicator) => {
                indicator.addEventListener('click', () => {
                    showHeroSlide(Number(indicator.dataset.heroIndicator));

                    if (!reducedMotion) {
                        restartRotation();
                    }
                });
            });

            if (!reducedMotion) {
                restartRotation();
            }
        }
    }

    const stickyHeaders = document.querySelectorAll('[data-site-header]');
    let headerScrollFrame = 0;

    const updateScrollState = () => {
        window.cancelAnimationFrame(headerScrollFrame);
        headerScrollFrame = window.requestAnimationFrame(() => {
            stickyHeaders.forEach((header) => header.classList.toggle('is-scrolled', window.scrollY > 24));
        });
    };

    window.addEventListener('scroll', updateScrollState, { passive: true });
    updateScrollState();

    const revealScope = document.querySelector('[data-reveal-scope]');

    if (revealScope && !reducedMotion && 'IntersectionObserver' in window) {
        const revealSelector = revealScope.dataset.revealScope === 'admin'
            ? 'section[class*="rounded"], article[class*="rounded"], [data-reveal-item]'
            : '.store-hero, .home-glass-card, .product-card, .catalog-filter-panel, .catalog-results-panel, .product-image-panel, .product-info-panel, .product-detail-card, .cart-items-panel, .cart-order-summary, .auth-card, .promotion-offer-card, .support-topic-card, section[class*="rounded"], article[class*="rounded"], [data-reveal-item]';
        const revealItems = revealScope.querySelectorAll(revealSelector);

        if (revealItems.length > 0) {
            document.body.classList.add('js-reveal');
            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-revealed');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

            revealItems.forEach((item, index) => {
                item.dataset.reveal = '';
                item.style.setProperty('--reveal-delay', `${(index % 4) * 45}ms`);
                revealObserver.observe(item);
            });
        }
    }

    const parallaxElements = reducedMotion ? [] : document.querySelectorAll('[data-parallax]');
    let parallaxFrame = 0;

    if (parallaxElements.length > 0) {
        const updateParallax = () => {
            window.cancelAnimationFrame(parallaxFrame);
            parallaxFrame = window.requestAnimationFrame(() => {
                parallaxElements.forEach((element) => {
                    const bounds = element.getBoundingClientRect();
                    const offset = Math.round(Math.max(-12, Math.min(12, (bounds.top + bounds.height / 2 - window.innerHeight / 2) * -0.025)));
                    const position = offset < 0 ? `calc(48% - ${Math.abs(offset)}px)` : `calc(48% + ${offset}px)`;
                    element.style.backgroundPosition = `center, center ${position}`;
                });
            });
        };

        window.addEventListener('scroll', updateParallax, { passive: true });
        updateParallax();
    }

    document.querySelectorAll('[data-flash]').forEach((flashStack) => {
        flashStack.classList.add('toast-stack');
        flashStack.setAttribute('aria-label', 'Thông báo');

        flashStack.querySelectorAll(':scope > *').forEach((toast) => {
            const isError = /rose|red/i.test(toast.className);
            toast.classList.add('toast-item');
            toast.setAttribute('role', isError ? 'alert' : 'status');
            toast.setAttribute('aria-live', isError ? 'assertive' : 'polite');

            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.dataset.toastClose = '';
            closeButton.setAttribute('aria-label', 'Đóng thông báo');
            closeButton.textContent = '×';
            closeButton.addEventListener('click', () => {
                toast.classList.add('is-dismissing');
                window.setTimeout(() => toast.remove(), 200);
            });
            toast.append(closeButton);

            if (!isError) {
                window.setTimeout(() => {
                    if (toast.isConnected) {
                        toast.classList.add('is-dismissing');
                        window.setTimeout(() => toast.remove(), 200);
                    }
                }, 5200);
            }
        });
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || form.dataset.skipLoading !== undefined || form.target === '_blank' || form.method.toLowerCase() === 'get') {
            return;
        }

        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }

        form.dataset.submitting = 'true';
        form.setAttribute('aria-busy', 'true');
    });

    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form[data-submitting]').forEach((form) => {
            delete form.dataset.submitting;
            form.removeAttribute('aria-busy');
        });
    });

    const guideRoot = document.querySelector('[data-guide-root]');

    if (guideRoot) {
        const guideVariant = guideRoot.dataset.guideVariant;
        const guideKey = `linhkien-guide-${guideVariant}-v1`;
        const guideSteps = guideVariant === 'admin'
            ? [
                { target: '[data-guide-target="admin-navigation"]', title: 'Đi nhanh đến khu vực cần quản lý', text: 'Menu quản trị thay đổi theo quyền tài khoản; những mục bạn được phép dùng sẽ nằm ở đây.' },
                { target: '[data-guide-target="overview-metrics"]', title: 'Theo dõi tình hình cửa hàng', text: 'Các chỉ số giúp bạn nhận biết đơn cần xử lý, doanh thu và cảnh báo tồn kho.' },
                { target: '[data-guide-target="overview-actions"]', title: 'Mở báo cáo liên quan', text: 'Từ đây bạn có thể chuyển nhanh tới danh sách đơn hàng hoặc tồn kho.' },
            ]
            : [
                { target: '[data-guide-target="search"]', title: 'Tìm linh kiện nhanh', text: 'Nhập tên, SKU hoặc MPN để xem gợi ý. Bộ lọc giúp thu hẹp kết quả.' },
                { target: '[data-guide-target="featured-categories"], [data-guide-target="catalog-filters"]', title: 'Duyệt theo nhóm linh kiện', text: 'Chọn danh mục hoặc thông số để tìm đúng linh kiện cho dự án.' },
                { target: '[data-guide-target="product-card"]', title: 'Xem chi tiết và thêm vào giỏ', text: 'Mở sản phẩm để xem thông số, số lượng tối thiểu và giá theo bậc.' },
            ];

        const guideButton = document.createElement('button');
        guideButton.type = 'button';
        guideButton.className = 'guide-trigger';
        guideButton.dataset.guideOpen = '';
        guideButton.textContent = '✦ Hướng dẫn nhanh';

        const guidePanel = document.createElement('section');
        guidePanel.className = 'guide-panel';
        guidePanel.dataset.guidePanel = '';
        guidePanel.hidden = true;
        guidePanel.setAttribute('role', 'dialog');
        guidePanel.setAttribute('aria-labelledby', 'guide-title');

        const guideProgress = document.createElement('p');
        guideProgress.className = 'text-[10px] font-bold uppercase tracking-wider text-blue-700';
        const guideTitle = document.createElement('h2');
        guideTitle.id = 'guide-title';
        const guideText = document.createElement('p');
        guideText.className = 'mb-0';
        const guideActions = document.createElement('div');
        guideActions.className = 'mt-4 flex items-center justify-between gap-3';
        const guideSkip = document.createElement('button');
        guideSkip.type = 'button';
        guideSkip.className = 'text-xs font-semibold text-slate-500 hover:text-slate-800';
        guideSkip.textContent = 'Bỏ qua';
        const guideNext = document.createElement('button');
        guideNext.type = 'button';
        guideNext.className = 'rounded-xl bg-blue-700 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-blue-800';
        guideNext.textContent = 'Tiếp theo';
        guideActions.append(guideSkip, guideNext);
        guidePanel.append(guideProgress, guideTitle, guideText, guideActions);
        guideRoot.append(guideButton, guidePanel);

        let guideStepIndex = 0;
        let activeGuideTarget = null;

        const getVisibleTarget = (selector) => Array.from(document.querySelectorAll(selector)).find((element) => element.getClientRects().length > 0);

        const closeGuide = (complete = false) => {
            guidePanel.hidden = true;
            activeGuideTarget?.classList.remove('guide-highlight');
            activeGuideTarget = null;

            if (complete) {
                try {
                    window.localStorage.setItem(guideKey, 'complete');
                } catch {
                    return;
                }
            }
        };

        const showGuideStep = () => {
            activeGuideTarget?.classList.remove('guide-highlight');
            activeGuideTarget = null;

            while (guideStepIndex < guideSteps.length) {
                const step = guideSteps[guideStepIndex];
                const target = getVisibleTarget(step.target);

                if (target) {
                    activeGuideTarget = target;
                    target.classList.add('guide-highlight');
                    target.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' });
                    guideTitle.textContent = step.title;
                    guideText.textContent = step.text;
                    guideProgress.textContent = `Bước ${guideStepIndex + 1} / ${guideSteps.length}`;
                    guideNext.textContent = guideStepIndex === guideSteps.length - 1 ? 'Hoàn tất' : 'Tiếp theo';
                    guidePanel.hidden = false;
                    return;
                }

                guideStepIndex++;
            }

            closeGuide(true);
        };

        const startGuide = () => {
            guideStepIndex = 0;
            showGuideStep();
        };

        guideButton.addEventListener('click', startGuide);
        guideSkip.addEventListener('click', () => closeGuide(true));
        guideNext.addEventListener('click', () => {
            guideStepIndex++;
            showGuideStep();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !guidePanel.hidden) {
                closeGuide();
                guideButton.focus();
            }
        });

        try {
            if (!window.localStorage.getItem(guideKey)) {
                window.setTimeout(startGuide, 850);
            }
        } catch {
            window.setTimeout(startGuide, 850);
        }
    }

    document.querySelectorAll('[data-product-image]').forEach((image) => {
        const showFallback = () => {
            image.hidden = true;
            const fallback = image.parentElement?.querySelector('[data-product-image-fallback]');

            if (fallback) {
                fallback.hidden = false;
            }
        };

        image.addEventListener('error', showFallback, { once: true });

        if (image.complete && image.naturalWidth === 0) {
            showFallback();
        }
    });

    document.querySelectorAll('[data-image-url-input]').forEach((input) => {
        const preview = document.querySelector('[data-image-preview]');
        const emptyState = document.querySelector('[data-image-preview-empty]');
        const errorState = document.querySelector('[data-image-preview-error]');

        if (!preview || !emptyState || !errorState) {
            return;
        }

        preview.addEventListener('load', () => {
            preview.hidden = false;
            emptyState.hidden = true;
            errorState.hidden = true;
        });
        preview.addEventListener('error', () => {
            preview.hidden = true;
            emptyState.hidden = true;
            errorState.hidden = false;
        });

        input.addEventListener('input', () => {
            const url = input.value.trim();
            errorState.hidden = true;
            emptyState.hidden = Boolean(url);
            preview.hidden = !url;

            if (url) {
                preview.src = url;
            }
        });

        if (preview.complete && preview.naturalWidth === 0 && preview.getAttribute('src')) {
            preview.hidden = true;
            emptyState.hidden = true;
            errorState.hidden = false;
        }
    });

    document.querySelectorAll('[data-search-form]').forEach((searchForm) => {
        const searchInput = searchForm.querySelector('[data-search-input]');
        const results = searchForm.querySelector('[data-search-results]');

        if (!searchInput || !results) {
            return;
        }

        let debounceTimer;
        let activeRequest;
        let activeOptionIndex = -1;

        const setExpanded = (isExpanded) => searchInput.setAttribute('aria-expanded', String(isExpanded));
        const getOptions = () => Array.from(results.querySelectorAll('[data-search-option]'));

        const showSkeleton = () => {
            results.replaceChildren();

            for (let index = 0; index < 3; index++) {
                const row = document.createElement('div');
                row.className = 'search-result-skeleton border-b border-slate-100 last:border-0';
                row.setAttribute('aria-hidden', 'true');
                row.innerHTML = '<span></span><span></span>';
                results.append(row);
            }

            results.setAttribute('aria-busy', 'true');
            results.classList.remove('hidden');
            setExpanded(true);
        };

        const showMessage = (message) => {
            const empty = document.createElement('p');
            empty.className = 'px-4 py-4 text-sm text-slate-500';
            empty.textContent = message;
            results.replaceChildren(empty);
            results.setAttribute('aria-busy', 'false');
            results.classList.remove('hidden');
            setExpanded(true);
        };

        const renderOptions = (items) => {
            results.replaceChildren();
            activeOptionIndex = -1;

            if (items.length === 0) {
                showMessage('Không tìm thấy sản phẩm phù hợp. Nhấn Enter để tìm thêm.');
                return;
            }

            items.forEach((item, index) => {
                const link = document.createElement('a');
                link.href = item.url;
                link.className = 'flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 last:border-0 hover:bg-blue-50 focus:bg-blue-50';
                link.id = `${results.id}-option-${index}`;
                link.dataset.searchOption = '';
                link.setAttribute('role', 'option');
                link.setAttribute('aria-selected', 'false');

                const detail = document.createElement('span');
                const name = document.createElement('strong');
                name.className = 'block text-sm text-slate-800';
                name.textContent = item.name;
                const sku = document.createElement('span');
                sku.className = 'mt-1 block font-mono text-[10px] text-slate-500';
                sku.textContent = item.sku;
                detail.append(name, sku);

                const price = document.createElement('span');
                price.className = 'shrink-0 text-xs font-bold text-blue-900';
                price.textContent = item.price;
                link.append(detail, price);
                results.append(link);
            });

            results.setAttribute('aria-busy', 'false');
            results.classList.remove('hidden');
            setExpanded(true);
        };

        searchInput.addEventListener('input', () => {
            window.clearTimeout(debounceTimer);
            activeRequest?.abort();
            activeOptionIndex = -1;
            searchInput.removeAttribute('aria-activedescendant');

            const term = searchInput.value.trim();

            if (term.length < 2) {
                results.classList.add('hidden');
                results.replaceChildren();
                results.setAttribute('aria-busy', 'false');
                setExpanded(false);
                return;
            }

            debounceTimer = window.setTimeout(async () => {
                activeRequest = new AbortController();
                showSkeleton();

                try {
                    const endpoint = new URL(searchForm.dataset.searchEndpoint, window.location.origin);
                    endpoint.searchParams.set('q', term);
                    const response = await fetch(endpoint, {
                        headers: { Accept: 'application/json' },
                        signal: activeRequest.signal,
                    });

                    if (!response.ok) {
                        throw new Error('Search request failed');
                    }

                    renderOptions(await response.json());
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        showMessage('Không tải được gợi ý. Nhấn Enter để xem kết quả.');
                    }
                }
            }, 220);
        });

        searchInput.addEventListener('keydown', (event) => {
            const options = getOptions();

            if (event.key === 'Escape') {
                window.clearTimeout(debounceTimer);
                activeRequest?.abort();
                results.classList.add('hidden');
                setExpanded(false);
                activeOptionIndex = -1;
                searchInput.removeAttribute('aria-activedescendant');
                return;
            }

            if (!options.length || !['ArrowDown', 'ArrowUp', 'Enter'].includes(event.key)) {
                return;
            }

            if (event.key === 'Enter' && activeOptionIndex >= 0) {
                event.preventDefault();
                window.location.assign(options[activeOptionIndex].href);
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                activeOptionIndex = event.key === 'ArrowDown'
                    ? (activeOptionIndex + 1) % options.length
                    : (activeOptionIndex - 1 + options.length) % options.length;

                options.forEach((option, index) => {
                    option.setAttribute('aria-selected', String(index === activeOptionIndex));
                });
                searchInput.setAttribute('aria-activedescendant', options[activeOptionIndex].id);
                options[activeOptionIndex].scrollIntoView({ block: 'nearest' });
            }
        });

        document.addEventListener('click', (event) => {
            if (!searchForm.contains(event.target)) {
                results.classList.add('hidden');
                setExpanded(false);
                searchInput.removeAttribute('aria-activedescendant');
            }
        });
    });
    document.querySelectorAll('[data-tier-prices]').forEach((form) => {
        const quantity = form.querySelector('[data-quantity-input]');
        const priceElement = form.closest('.product-detail')?.querySelector('[data-product-price]')
            ?? document.querySelector('[data-product-price]');

        if (!quantity || !priceElement) {
            return;
        }

        let tiers = [];

        try {
            tiers = JSON.parse(form.dataset.tierPrices ?? '[]');
        } catch {
            return;
        }

        const formatVnd = (amount) => `${new Intl.NumberFormat('vi-VN').format(amount)}₫`;
        const updatePrice = () => {
            const amount = Number.parseInt(quantity.value || '1', 10);
            const tier = tiers
                .filter((item) => amount >= Number(item.min) && (item.max === null || amount <= Number(item.max)))
                .sort((left, right) => Number(right.min) - Number(left.min))[0];
            const price = tier ? Number(tier.price) : Number(form.dataset.basePrice);
            priceElement.textContent = formatVnd(price);
        };

        quantity.addEventListener('input', updatePrice);
    });

    document.querySelectorAll('[data-file-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const label = input.closest('[data-dropzone]')?.querySelector('[data-file-label]');
            if (label && input.files?.[0]) {
                label.textContent = input.files[0].name;
            }
        });
    });

    document.querySelectorAll('[data-map-marker]').forEach((marker) => {
        const popup = document.getElementById(marker.getAttribute('aria-controls'));
        const closeButton = popup?.querySelector('[data-map-close]');

        if (!popup) {
            return;
        }

        const setPopupOpen = (isOpen) => {
            popup.hidden = !isOpen;
            marker.setAttribute('aria-expanded', String(isOpen));
        };

        marker.addEventListener('click', () => setPopupOpen(popup.hidden));
        closeButton?.addEventListener('click', () => {
            setPopupOpen(false);
            marker.focus();
        });
        document.addEventListener('click', (event) => {
            if (!popup.hidden && !popup.contains(event.target) && !marker.contains(event.target)) {
                setPopupOpen(false);
            }
        });
        marker.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !popup.hidden) {
                setPopupOpen(false);
            }
        });
    });

    const administrativeApi = 'https://tinhthanhpho.com/api/v1';

    const getAdministrativeUnits = async (url) => {
        const response = await fetch(url, { headers: { Accept: 'application/json' } });

        if (!response.ok) {
            throw new Error('Could not load administrative data');
        }

        const payload = await response.json();

        if (!payload.success || !Array.isArray(payload.data)) {
            throw new Error('Invalid administrative data');
        }

        return payload.data;
    };

    document.querySelectorAll('[data-address-geo]').forEach((addressForm) => {
        const provinceSelect = addressForm.querySelector('[data-province-select]');
        const wardSelect = addressForm.querySelector('[data-ward-select]');
        const provinceNameInput = addressForm.querySelector('[data-province-name]');
        const wardNameInput = addressForm.querySelector('[data-ward-name]');
        const errorMessage = addressForm.parentElement?.querySelector('[data-address-api-error]');
        const retryButton = errorMessage?.querySelector('[data-address-api-retry]');
        const savedAddressSelect = addressForm.closest('form')?.querySelector('[data-saved-address-select]');

        if (!provinceSelect || !wardSelect || !provinceNameInput || !wardNameInput) {
            return;
        }

        const setOptions = (select, units, placeholder) => {
            select.replaceChildren(new Option(placeholder, ''));
            units.forEach((unit) => {
                const label = unit.type ? `${unit.type} ${unit.name}` : unit.name;
                select.add(new Option(label, unit.code));
            });
        };

        const showApiError = () => {
            if (errorMessage) {
                errorMessage.hidden = false;
            }
        };

        const loadWards = async (provinceCode, selectedWardCode = '') => {
            wardNameInput.value = '';

            if (!provinceCode) {
                setOptions(wardSelect, [], 'Chọn tỉnh/thành trước');
                wardSelect.disabled = true;
                delete wardSelect.dataset.loading;
                return;
            }

            setOptions(wardSelect, [], 'Đang tải danh sách xã/phường...');
            wardSelect.disabled = true;
            wardSelect.dataset.loading = 'true';

            try {
                const wards = await getAdministrativeUnits(
                    `${administrativeApi}/new-provinces/${encodeURIComponent(provinceCode)}/wards?limit=200`,
                );
                setOptions(wardSelect, wards, 'Chọn xã/phường');
                wardSelect.value = selectedWardCode;
                wardSelect.disabled = false;
                delete wardSelect.dataset.loading;
                const selectedWard = wards.find((ward) => ward.code === wardSelect.value);
                wardNameInput.value = selectedWard ? `${selectedWard.type} ${selectedWard.name}` : '';
            } catch {
                setOptions(wardSelect, [], 'Không tải được xã/phường');
                wardSelect.disabled = true;
                delete wardSelect.dataset.loading;
                showApiError();
            }
        };

        const selectProvince = async (provinceCode, wardCode = '') => {
            provinceSelect.value = provinceCode;
            const selectedProvince = Array.from(provinceSelect.options).find((option) => option.value === provinceCode);
            provinceNameInput.value = selectedProvince?.textContent ?? '';
            await loadWards(provinceCode, wardCode);
        };

        const initialize = async () => {
            if (errorMessage) {
                errorMessage.hidden = true;
            }
            provinceSelect.disabled = true;
            provinceSelect.dataset.loading = 'true';
            wardSelect.disabled = true;
            setOptions(provinceSelect, [], 'Đang tải danh sách tỉnh/thành...');

            try {
                const provinces = await getAdministrativeUnits(`${administrativeApi}/new-provinces?limit=100`);
                setOptions(provinceSelect, provinces, 'Chọn tỉnh/thành phố');
                provinceSelect.disabled = false;
                delete provinceSelect.dataset.loading;

                const initialProvince = addressForm.dataset.initialProvince ?? '';
                const initialWard = addressForm.dataset.initialWard ?? '';

                if (initialProvince) {
                    await selectProvince(initialProvince, initialWard);
                } else {
                    provinceNameInput.value = '';
                    await loadWards('');
                }
            } catch {
                setOptions(provinceSelect, [], 'Không tải được tỉnh/thành phố');
                provinceSelect.disabled = true;
                delete provinceSelect.dataset.loading;
                showApiError();
            }
        };

        provinceSelect.addEventListener('change', () => {
            const selectedProvince = Array.from(provinceSelect.options).find((option) => option.value === provinceSelect.value);
            provinceNameInput.value = selectedProvince?.textContent ?? '';
            loadWards(provinceSelect.value);
        });

        wardSelect.addEventListener('change', () => {
            const selectedWard = wardSelect.selectedOptions[0];
            wardNameInput.value = selectedWard?.value ? selectedWard.textContent : '';
        });

        savedAddressSelect?.addEventListener('change', async () => {
            const selectedAddress = savedAddressSelect.selectedOptions[0];

            if (!selectedAddress?.value) {
                provinceSelect.value = '';
                provinceNameInput.value = '';
                await loadWards('');
                const checkoutForm = addressForm.closest('form');
                const addressLineInput = checkoutForm?.querySelector('[name="shipping_address"]');
                const noteInput = checkoutForm?.querySelector('[name="customer_note"]');

                if (addressLineInput) {
                    addressLineInput.value = '';
                }

                if (noteInput) {
                    noteInput.value = '';
                }
                return;
            }

            provinceNameInput.value = selectedAddress.dataset.provinceName ?? '';
            addressForm.dataset.initialProvince = selectedAddress.dataset.provinceCode ?? '';
            addressForm.dataset.initialWard = selectedAddress.dataset.wardCode ?? '';
            await selectProvince(addressForm.dataset.initialProvince, addressForm.dataset.initialWard);
            wardNameInput.value = selectedAddress.dataset.wardName ?? '';

            const checkoutForm = addressForm.closest('form');
            const fields = {
                customer_name: selectedAddress.dataset.recipientName,
                customer_phone: selectedAddress.dataset.recipientPhone,
                shipping_address: selectedAddress.dataset.addressLine,
                customer_note: selectedAddress.dataset.note,
            };

            Object.entries(fields).forEach(([name, value]) => {
                const field = checkoutForm?.querySelector(`[name="${name}"]`);

                if (field) {
                    field.value = value ?? '';
                }
            });
        });

        retryButton?.addEventListener('click', initialize);
        initialize();
    });
});

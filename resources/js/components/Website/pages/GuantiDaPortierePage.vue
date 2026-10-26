<template>
    <section
        class="ks-plp"
        :class="{
            'ks-plp--phone': plpIsPhone,
            'ks-plp--phone-wide': plpPhoneWideLayout,
            'ks-plp--has-sidebar': plpShowSidebar,
            'ks-plp--filter-open': Boolean(openFilterKey),
        }"
        :lang="pageLang === 'ar' ? 'ar' : 'en'"
    >
        <div class="ks-plp-inner">
            <nav class="ks-plp-bc" aria-label="Breadcrumb">
                <router-link :to="`/${locale}/`">{{ lt.breadcrumb_home }}</router-link>
                <span class="ks-plp-bc-sep">/</span>
                <span class="ks-plp-bc-current">{{ pageTitle }}</span>
            </nav>

            <p v-if="errorMsg" class="ks-plp-error" role="alert">{{ errorMsg }}</p>

            <div v-else class="ks-plp-split">

                <!-- القائمة الجانبية: التصنيفات من الـ API (لابتوب فقط) -->
                <aside v-if="plpShowSidebar" class="ks-plp-side" aria-labelledby="ks-plp-cat-heading">
                    <h2 id="ks-plp-cat-heading" class="ks-plp-side-title">{{ lt.categories_title }}</h2>
                    <ul class="ks-plp-cat-tree">
                        <li v-for="main in sidebarMainItems" :key="main.slug" class="ks-plp-cat-root">
                            <div class="ks-plp-cat-main-row">
                                <router-link
                                    :to="`/${locale}/${main.slug}`"
                                    class="ks-plp-cat-link"
                                    :class="{ 'is-active': isMainActive(main.slug) }"
                                    @click="onMainCategoryClick(main.slug)"
                                >
                                    {{ main.label }}
                                </router-link>
                            </div>
                            <ul
                                v-if="desktopShowCategoryChildren(main)"
                                class="ks-plp-cat-children"
                            >
                                <li v-for="ch in main.children" :key="ch.id">
                                    <a
                                        v-if="ch.filterIds"
                                        href="#"
                                        class="ks-plp-cat-sublink"
                                        :class="{ 'is-active': isTeamsportSubActive(ch.id) }"
                                        @click.prevent="selectTeamsportSub(ch)"
                                    >
                                        {{ ch.name }}
                                    </a>
                                    <router-link
                                        v-else
                                        :to="plpPathForShopChild(main.slug, ch)"
                                        class="ks-plp-cat-sublink"
                                        :class="{ 'is-active': isChildActive(ch) }"
                                        @click="onChildCategoryClick(main.slug, ch)"
                                    >
                                        {{ ch.name }}
                                    </router-link>
                                    <!-- Level 3: grandchildren (teamsport filterIds أو فروع المعدات) -->
                                    <ul
                                        v-if="ch.children?.length && (ch.filterIds ? isTeamsportSubActive(ch.id) : isEquipmentBranchExpanded(main.slug, ch))"
                                        class="ks-plp-cat-grandchildren"
                                    >
                                        <li v-for="gc in ch.children" :key="gc.slug || gc.id">
                                            <a
                                                v-if="ch.filterIds"
                                                href="#"
                                                class="ks-plp-cat-sublink ks-plp-cat-sublink--l3"
                                                :class="{ 'is-active': selectedTeamsportSub === (ch.id + '/' + gc.slug) }"
                                                @click.prevent="selectTeamsportGrandchild(ch, gc)"
                                            >
                                                {{ gc.label || gc.name }}
                                            </a>
                                            <router-link
                                                v-else
                                                :to="plpPathForShopChild(main.slug, gc)"
                                                class="ks-plp-cat-sublink ks-plp-cat-sublink--l3"
                                                :class="{ 'is-active': isChildActive(gc) }"
                                                @click="onChildCategoryClick(main.slug, gc)"
                                            >
                                                {{ gc.label || gc.name }}
                                            </router-link>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </aside>

                <div class="ks-plp-main">
                    <header v-if="plpShowCategoryTitle" class="ks-plp-head">
                        <h1 class="ks-plp-title">
                            {{ pageTitle }}
                            <span class="ks-plp-title-count" lang="en" dir="ltr">{{ pagination.total || 0 }}</span>
                        </h1>
                    </header>
                    <p v-if="loading && products.length" class="ks-plp-inline-loading">{{ lt.loading }}</p>
                    <div v-if="facets.brands?.length" class="ks-plp-brands-wrap ks-plp-brands-desktop">
                        <button
                            type="button"
                            class="ks-plp-brands-arrow ks-plp-brands-arrow--prev"
                            :class="{ 'ks-plp-brands-arrow--muted': !brandStripCanScrollPrev }"
                            :aria-label="lt.scroll_prev"
                            :disabled="!brandStripCanScrollPrev"
                            @click="scrollBrands(-1)"
                        >
                            ‹
                        </button>
                        <div ref="brandStripRef" class="ks-plp-brands-track" @scroll="updateBrandStripOverflow">
                            <button
                                v-for="b in facets.brands"
                                :key="b.id"
                                type="button"
                                class="ks-brand-chip"
                                :class="{ 'ks-brand-chip--active': selectedBrands.includes(b.id) }"
                                @click="selectBrand(b.id)"
                            >
                                {{ b.name }}
                            </button>
                        </div>
                        <button
                            type="button"
                            class="ks-plp-brands-arrow ks-plp-brands-arrow--next"
                            :class="{ 'ks-plp-brands-arrow--muted': !brandStripOverflow }"
                            :aria-label="lt.scroll_next"
                            :disabled="!brandStripOverflow"
                            @click="scrollBrands(1)"
                        >
                            ›
                        </button>
                    </div>

                    <div class="ks-plp-filters-scroll" :class="{ 'is-dropdown-open': Boolean(openFilterKey) }">
                    <div class="ks-plp-topbar ks-plp-topbar--desktop">
                        <div
                            v-for="dd in quickFilterDropdowns"
                            :key="`quick-${dd.key}`"
                            class="ks-plp-dd"
                            :class="{ 'is-open': openFilterKey === dd.key }"
                        >
                            <button type="button" class="ks-plp-dd-trigger" @click="toggleDropdownMenu(dd.key)">
                                <span>{{ dd.label }}</span>
                                <span class="ks-plp-dd-caret" aria-hidden="true" />
                            </button>
                            <div v-if="openFilterKey === dd.key" class="ks-plp-dd-menu ks-plp-dd-menu--facet">
                                <label v-for="opt in dd.options" :key="`${dd.key}:${opt.value}`" class="ks-plp-dd-option">
                                    <input
                                        type="checkbox"
                                        :checked="isDropdownChecked(dd, opt)"
                                        @change="toggleDropdownOption(dd, opt)"
                                    />
                                    <span>{{ opt.label }}</span>
                                    <span v-if="opt.count != null" class="ks-plp-dd-opt-count"><span lang="en" dir="ltr">{{ opt.count }}</span></span>
                                </label>
                                <button type="button" class="ks-plp-dd-done" @click="closeFilterDropdown">
                                    ✓ {{ lt.filter_done }}
                                </button>
                            </div>
                        </div>

                        <div v-if="extraFilterDropdowns.length" class="ks-plp-dd">
                            <button type="button" class="ks-plp-dd-trigger ks-plp-dd-trigger--primary" @click="showExtraFilters = !showExtraFilters">
                                <span>{{ locale === "ar" ? "المزيد من الفلاتر" : "More filters" }}</span>
                                <span class="ks-plp-dd-caret ks-plp-dd-caret--more" aria-hidden="true" />
                            </button>
                        </div>

                        <div
                            v-for="dd in (showExtraFilters ? extraFilterDropdowns : [])"
                            :key="`extra-${dd.key}`"
                            class="ks-plp-dd"
                            :class="{ 'is-open': openFilterKey === dd.key }"
                        >
                            <button type="button" class="ks-plp-dd-trigger" @click="toggleDropdownMenu(dd.key)">
                                <span>{{ dd.label }}</span>
                                <span class="ks-plp-dd-caret" aria-hidden="true" />
                            </button>
                            <div v-if="openFilterKey === dd.key" class="ks-plp-dd-menu ks-plp-dd-menu--facet">
                                <label v-for="opt in dd.options" :key="`${dd.key}:${opt.value}`" class="ks-plp-dd-option">
                                    <input
                                        type="checkbox"
                                        :checked="isDropdownChecked(dd, opt)"
                                        @change="toggleDropdownOption(dd, opt)"
                                    />
                                    <span>{{ opt.label }}</span>
                                    <span v-if="opt.count != null" class="ks-plp-dd-opt-count"><span lang="en" dir="ltr">{{ opt.count }}</span></span>
                                </label>
                                <button type="button" class="ks-plp-dd-done" @click="closeFilterDropdown">
                                    ✓ {{ lt.filter_done }}
                                </button>
                            </div>
                        </div>

                        <div class="ks-plp-dd ks-plp-dd--sort" :class="{ 'is-open': openFilterKey === 'sort-menu' }">
                            <button type="button" class="ks-plp-dd-trigger" @click="toggleDropdownMenu('sort-menu')">
                                <span>{{ sortTriggerLabel }}</span>
                                <span class="ks-plp-dd-caret" aria-hidden="true" />
                            </button>
                            <div v-if="openFilterKey === 'sort-menu'" class="ks-plp-dd-menu ks-plp-dd-menu--sort">
                                <button
                                    type="button"
                                    class="ks-plp-sort-opt"
                                    :class="{ 'is-active': sort === 'relevance' }"
                                    @click="setSort('relevance')"
                                >
                                    {{ lt.sort_popular }}
                                </button>
                                <button
                                    type="button"
                                    class="ks-plp-sort-opt"
                                    :class="{ 'is-active': sort === 'newest' }"
                                    @click="setSort('newest')"
                                >
                                    {{ lt.sort_newest }}
                                </button>
                                <button
                                    type="button"
                                    class="ks-plp-sort-opt"
                                    :class="{ 'is-active': sort === 'price_desc' }"
                                    @click="setSort('price_desc')"
                                >
                                    {{ lt.sort_price_down }}
                                </button>
                                <button
                                    type="button"
                                    class="ks-plp-sort-opt"
                                    :class="{ 'is-active': sort === 'price_asc' }"
                                    @click="setSort('price_asc')"
                                >
                                    {{ lt.sort_price_up }}
                                </button>
                                <button
                                    type="button"
                                    class="ks-plp-sort-opt"
                                    :class="{ 'is-active': sort === 'discount_desc' }"
                                    @click="setSort('discount_desc')"
                                >
                                    {{ lt.sort_discount_down }}
                                </button>
                            </div>
                        </div>
                    </div>
                    </div>

                    <div v-if="plpShowMobileSticky" class="ks-plp-mobile-sticky-bar">
                        <div class="ks-plp-mobile-plp-tools">
                            <button type="button" class="ks-plp-mobile-split-btn" @click.stop="openMobileSubcategorySheet">
                                <span class="ks-plp-mobile-split-label">{{ lt.categories_title }}</span>
                                <span class="ks-plp-mobile-split-caret" aria-hidden="true" />
                            </button>
                            <button type="button" class="ks-plp-mobile-split-btn ks-plp-mobile-split-filter" @click.stop="openMobileFilterHub">
                                <svg class="ks-plp-mobile-filter-ico" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                                    <path
                                        fill="currentColor"
                                        d="M4 6h16v2H4V6zm3 5h10v2H7v-2zm3 5h4v2h-4v-2z"
                                    />
                                </svg>
                                <span>{{ lt.filter_sheet }}</span>
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="loading && !products.length"
                        class="ks-plp-skeleton"
                        role="status"
                        aria-live="polite"
                        :aria-label="lt.loading"
                    >
                        <div :class="viewMode === 'list' ? 'ks-plp-list' : 'ks-plp-grid'">
                            <div
                                v-for="i in plpSkeletonCount"
                                :key="'plp-skel-' + i"
                                class="ks-plp-skeleton-card"
                                :class="{ 'ks-plp-skeleton-card--list': viewMode === 'list' }"
                            >
                                <div class="ks-plp-skeleton-img ks-plp-skel-shimmer" aria-hidden="true" />
                                <div class="ks-plp-skeleton-body">
                                    <div class="ks-plp-skeleton-line ks-plp-skel-shimmer ks-plp-skeleton-line--brand" aria-hidden="true" />
                                    <div class="ks-plp-skeleton-line ks-plp-skel-shimmer ks-plp-skeleton-line--title" aria-hidden="true" />
                                    <div class="ks-plp-skeleton-line ks-plp-skel-shimmer ks-plp-skeleton-line--price" aria-hidden="true" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else-if="!loading && !products.length" class="ks-plp-state">{{ lt.empty }}</p>

                    <div
                        v-else
                        :class="[
                            viewMode === 'grid' ? 'ks-plp-grid' : 'ks-plp-list',
                            loading && products.length ? 'ks-plp-product-list--refreshing' : '',
                        ]"
                    >
                        <article
                            v-for="(p, idx) in products"
                            :key="p.sku"
                            class="ks-plp-card"
                            :class="{ 'ks-plp-card--list': viewMode === 'list' }"
                            role="link"
                            tabindex="0"
                            @mouseenter="onCardMouseEnter(p)"
                            @mouseleave="onCardMouseLeave"
                            @touchstart.passive="prefetchProduct(p)"
                            @click="onPlpCardClick(p, $event)"
                            @keydown.enter.prevent="goToProduct(p)"
                            @keydown.space.prevent="goToProduct(p)"
                        >
                            <div class="ks-plp-card-img-wrap">
                                <span
                                    v-if="hasDiscount(p) && discountPercent(p) > 0"
                                    class="ks-plp-card-sale-flag"
                                >
                                    <span lang="en" dir="ltr">{{ discountPercent(p) }}%</span>
                                </span>
                                <img
                                    :key="`${productCardKey(p)}-${cardImageIndex[productCardKey(p)] || 0}`"
                                    :src="cardImageSrc(p)"
                                    :alt="p.name"
                                    class="ks-plp-card-img"
                                    :loading="idx < 12 ? 'eager' : 'lazy'"
                                    decoding="async"
                                    referrerpolicy="no-referrer"
                                    draggable="false"
                                    @dragstart.prevent
                                />
                                <div
                                    v-if="productCardImages(p).length > 1 && plpCardImageSwipeEnabled"
                                    class="ks-plp-img-swipe-layer"
                                    aria-hidden="true"
                                    @mousedown="onCardImgMouseDown(p, $event)"
                                    @dragstart.prevent
                                />
                                <div
                                    v-if="
                                        productCardImages(p).length > 1 &&
                                        plpCardImageSwipeEnabled &&
                                        (hoveredSku === p.sku || cardImgActiveSku === productCardKey(p))
                                    "
                                    class="ks-plp-img-nav"
                                >
                                    <button
                                        type="button"
                                        class="ks-plp-img-nav-btn"
                                        @click.prevent.stop="slideCardImage(p, -1)"
                                    >
                                        ‹
                                    </button>
                                    <button
                                        type="button"
                                        class="ks-plp-img-nav-btn"
                                        @click.prevent.stop="slideCardImage(p, 1)"
                                    >
                                        ›
                                    </button>
                                </div>
                            </div>
                            <div class="ks-plp-card-body">
                                <p class="ks-plp-card-brand">{{ p.brand?.name }}</p>
                                <p class="ks-plp-card-name">{{ p.name }}</p>
                                <div class="ks-plp-card-price">
                                    <span
                                        v-if="hasDiscount(p)"
                                        class="ks-plp-price-old ks-price-strike"
                                        v-html="formatPriceHtml(p.price.old, p.price.currency)"
                                    ></span>
                                    <p
                                        class="ks-plp-card-price-now"
                                        :class="{ 'is-sale': hasDiscount(p) }"
                                        v-html="pricePromoHtml(p.price.current, p.price.currency)"
                                    ></p>
                                </div>
                                <p v-if="hasDiscount(p)" class="ks-plp-price-after">
                                    {{ locale === "ar" ? "السعر بعد الخصم" : "after discount" }}
                                </p>
                                <p v-if="hasDiscount(p)" class="ks-plp-limited-time">limited time only</p>
                            </div>
                        </article>
                    </div>

                    <nav
                        v-if="pagination.last_page > 1"
                        class="ks-plp-pager ks-plp-pager--numbered"
                        :aria-label="locale === 'ar' ? 'صفحات النتائج' : 'Product pages'"
                        lang="en"
                        dir="ltr"
                    >
                        <button
                            type="button"
                            class="ks-plp-pager-arrow"
                            :disabled="pagination.page <= 1 || loading"
                            :aria-label="lt.pager_prev"
                            @click="goPage(pagination.page - 1)"
                        >
                            ‹
                        </button>
                        <ol class="ks-plp-pager-pages">
                            <li v-for="p in pagerWindowedPages" :key="'p-' + p">
                                <button
                                    type="button"
                                    class="ks-plp-pager-num"
                                    :class="{ 'is-active': p === pagination.page }"
                                    :disabled="loading && p !== pagination.page"
                                    :aria-current="p === pagination.page ? 'page' : undefined"
                                    :aria-label="(locale === 'ar' ? 'صفحة ' : 'Page ') + p"
                                    @click="goPage(p)"
                                >
                                    {{ p }}
                                </button>
                            </li>
                        </ol>
                        <button
                            type="button"
                            class="ks-plp-pager-arrow"
                            :disabled="pagination.page >= pagination.last_page || loading"
                            :aria-label="lt.pager_next"
                            @click="goPage(pagination.page + 1)"
                        >
                            ›
                        </button>
                    </nav>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="mobileSubcategoryOpen" class="ks-mob-backdrop" @click.self="onMobileBackdropSelfClick('subcategory')">
                <aside
                    class="ks-mob-drawer"
                    role="dialog"
                    :aria-modal="true"
                    :aria-label="lt.categories_title"
                    :lang="locale === 'ar' ? 'ar' : 'en'"
                    :dir="locale === 'ar' ? 'rtl' : 'ltr'"
                    tabindex="-1"
                    @click.stop
                    @keydown.escape.prevent="closeMobileSubcategorySheet"
                >
                    <div class="ks-mob-drawer-scroll">
                        <div v-for="main in sidebarMainItems" :key="'mobcat-' + main.slug" class="ks-mob-cat-block">
                            <router-link class="ks-mob-cat-head" :to="`/${locale}/${main.slug}`" @click="closeMobileSubcategorySheet">
                                {{ main.label }}
                            </router-link>
                            <ul v-if="main.children?.length" class="ks-mob-cat-sublist">
                                <li v-for="ch in main.children" :key="'mobsub-' + ch.id">
                                    <a
                                        v-if="ch.filterIds"
                                        href="#"
                                        class="ks-mob-cat-sub-parent"
                                        :class="{ 'is-active': isTeamsportSubActive(ch.id) }"
                                        @click.prevent="selectTeamsportSub(ch); closeMobileSubcategorySheet()"
                                    >
                                        {{ ch.name }}
                                    </a>
                                    <ul v-if="ch.filterIds && ch.children?.length" class="ks-mob-cat-l3">
                                        <li v-for="gc in ch.children" :key="'mobgc-' + gc.slug">
                                            <a
                                                href="#"
                                                @click.prevent="selectTeamsportGrandchild(ch, gc); closeMobileSubcategorySheet()"
                                            >
                                                {{ gc.label || gc.name }}
                                            </a>
                                        </li>
                                    </ul>
                                    <ul v-else-if="!ch.filterIds && ch.children?.length" class="ks-mob-cat-l3">
                                        <li v-for="gc in ch.children" :key="'mobgc-eq-' + (gc.slug || gc.id)">
                                            <router-link
                                                :to="plpPathForShopChild(main.slug, gc)"
                                                @click="closeMobileSubcategorySheet"
                                            >
                                                {{ gc.label || gc.name }}
                                            </router-link>
                                        </li>
                                    </ul>
                                    <router-link
                                        v-if="!ch.filterIds"
                                        :to="plpPathForShopChild(main.slug, ch)"
                                        @click="closeMobileSubcategorySheet"
                                    >
                                        {{ ch.name }}
                                    </router-link>
                                </li>
                            </ul>
                        </div>
                        <router-link class="ks-mob-cat-sale-inline" :to="`/${locale}/sale`" @click="closeMobileSubcategorySheet">
                            {{ lt.sale_link }}
                        </router-link>
                    </div>
                    <button type="button" class="ks-mob-cta ks-mob-cta--footer" @click="applyShowProductsCloseSubcategory">
                        {{ showProductsCtaLabel }}
                    </button>
                </aside>
            </div>

            <div v-if="mobileFilterHubOpen && !mobileFilterDetailKey" class="ks-mob-backdrop ks-mob-backdrop--hub" @click.self="onMobileBackdropSelfClick('hub')">
                <div
                    class="ks-mob-hub-sheet"
                    role="dialog"
                    aria-modal="true"
                    :dir="locale === 'ar' ? 'rtl' : 'ltr'"
                    @click.stop
                    @keydown.escape.prevent="closeMobileFilterHub"
                >
                    <div class="ks-mob-hub-scroll">
                        <button
                            v-for="row in mobileHubRows"
                            :key="'hub-' + row.key"
                            type="button"
                            class="ks-mob-hub-row"
                            :disabled="row.disabled"
                            @click="row.disabled ? null : openMobileFilterDetail(row.key, true)"
                        >
                            <div class="ks-mob-hub-row-text">
                                <div class="ks-mob-hub-row-title">{{ row.title }}</div>
                                <div class="ks-mob-hub-row-sub">{{ row.subtitle }}</div>
                            </div>
                            <span class="ks-mob-hub-chevron" aria-hidden="true">›</span>
                        </button>
                    </div>
                    <button type="button" class="ks-mob-cta ks-mob-cta--footer ks-mob-cta--check" @click="applyShowProductsCloseHub">
                        {{ showProductsCtaLabel }}
                    </button>
                </div>
            </div>

            <div v-if="mobileFilterDetailKey" class="ks-mob-backdrop ks-mob-backdrop--detail" @click.self="onMobileBackdropSelfClick('detail')">
                <div
                    class="ks-mob-detail-sheet"
                    role="dialog"
                    aria-modal="true"
                    :dir="locale === 'ar' ? 'rtl' : 'ltr'"
                    @click.stop
                    @keydown.escape.prevent="backdropCloseMobileDetail"
                >
                    <header class="ks-mob-detail-head">
                        <button v-if="mobileDetailOpenedFromHub" type="button" class="ks-mob-detail-back" @click="closeMobileDetailToHub">
                            ‹
                        </button>
                        <h2 class="ks-mob-detail-title">{{ mobileFilterDetailTitle }}</h2>
                    </header>

                    <div class="ks-mob-detail-body">
                        <template v-if="mobileFilterDetailKey === 'sort'">
                            <button
                                type="button"
                                class="ks-mob-radio-row"
                                :class="{ 'is-on': sort === 'relevance' }"
                                @click="setSortPreserveDetail('relevance')"
                            >
                                {{ lt.sort_popular }}
                            </button>
                            <button
                                type="button"
                                class="ks-mob-radio-row"
                                :class="{ 'is-on': sort === 'newest' }"
                                @click="setSortPreserveDetail('newest')"
                            >
                                {{ lt.sort_newest }}
                            </button>
                            <button
                                type="button"
                                class="ks-mob-radio-row"
                                :class="{ 'is-on': sort === 'price_desc' }"
                                @click="setSortPreserveDetail('price_desc')"
                            >
                                {{ lt.sort_price_down }}
                            </button>
                            <button
                                type="button"
                                class="ks-mob-radio-row"
                                :class="{ 'is-on': sort === 'price_asc' }"
                                @click="setSortPreserveDetail('price_asc')"
                            >
                                {{ lt.sort_price_up }}
                            </button>
                            <button
                                type="button"
                                class="ks-mob-radio-row"
                                :class="{ 'is-on': sort === 'discount_desc' }"
                                @click="setSortPreserveDetail('discount_desc')"
                            >
                                {{ lt.sort_discount_down }}
                            </button>
                        </template>

                        <template v-else-if="mobileFilterDetailKey === 'brand'">
                            <label v-for="b in facets.brands" :key="'mb-' + b.id" class="ks-mob-check-row">
                                <input type="checkbox" :checked="selectedBrands.includes(b.id)" @change="toggleBrandMobile(b.id, $event)" />
                                <span class="ks-mob-check-label">{{ b.name }}</span>
                                <span v-if="countBadge(b.count) != null" class="ks-mob-count"><span lang="en" dir="ltr">{{ countBadge(b.count) }}</span></span>
                            </label>
                        </template>

                        <template v-else-if="mobileFilterDetailKey === 'sizes' && facetRowByKey('sizes')">
                            <label
                                v-for="opt in facetRowByKey('sizes').options"
                                :key="'msz-' + opt.value"
                                class="ks-mob-check-row"
                            >
                                <input
                                    type="checkbox"
                                    :checked="selectedSizes.includes(opt.value)"
                                    @change="toggleDropdownOption(facetRowByKey('sizes'), opt)"
                                />
                                <span class="ks-mob-check-label">{{ opt.label }}</span>
                                <span v-if="countBadge(opt.count) != null" class="ks-mob-count"><span lang="en" dir="ltr">{{ countBadge(opt.count) }}</span></span>
                            </label>
                        </template>

                        <template v-else-if="mobileFilterDetailKey === 'colors' && facetRowByKey('colors')">
                            <label
                                v-for="opt in facetRowByKey('colors').options"
                                :key="'mcl-' + opt.value"
                                class="ks-mob-check-row"
                            >
                                <input
                                    type="checkbox"
                                    class="ks-mob-color-radio"
                                    :checked="selectedColors.includes(opt.value)"
                                    @change="toggleColorMobile(opt.value, $event)"
                                />
                                <span class="ks-mob-check-label">{{ opt.label }}</span>
                                <span v-if="countBadge(opt.count) != null" class="ks-mob-count"><span lang="en" dir="ltr">{{ countBadge(opt.count) }}</span></span>
                            </label>
                        </template>

                        <template v-else-if="mobileFilterDetailKey === 'subcategory'">
                            <router-link
                                v-for="ch in currentParentMainForSub?.children || []"
                                :key="'sd-' + ch.id"
                                class="ks-mob-subcat-link"
                                :to="plpPathForShopChild(currentParentMainForSub?.slug || '', ch)"
                                @click="closeMobileFilterDetailAll"
                            >
                                {{ ch.name }}
                            </router-link>
                        </template>

                        <template v-else-if="mobileFilterDetailKey === 'price'">
                            <p class="ks-mob-muted">{{ lt.price_unavailable }}</p>
                        </template>

                        <template v-else-if="mobileActiveFacetRow">
                            <label
                                v-for="opt in mobileActiveFacetRow.options"
                                :key="'mat-' + mobileActiveFacetRow.key + opt.value"
                                class="ks-mob-check-row"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isDropdownChecked(mobileActiveFacetRow, opt)"
                                    @change="toggleDropdownOption(mobileActiveFacetRow, opt)"
                                />
                                <span class="ks-mob-check-label">{{ opt.label }}</span>
                                <span v-if="countBadge(opt.count) != null" class="ks-mob-count"><span lang="en" dir="ltr">{{ countBadge(opt.count) }}</span></span>
                            </label>
                        </template>
                    </div>

                    <button type="button" class="ks-mob-done-fab" @click="doneMobileFilterDetail">{{ lt.filter_done }}</button>
                </div>
            </div>
        </Teleport>
    </section>
</template>

<script>
import { fetchCatalogCategories, fetchCatalogProducts, fetchCatalogShopListing, prefetchCatalogProductBySlug } from "@/utils/catalogApi";
import { glovesPlpCopy } from "@/constants/guantiDaPortiereI18n.js";
import { embedWesternDigits, formatWebsiteCurrency, formatWebsiteCurrencyParts } from "@/utils/westernNumerals";
import {
    formatWebsitePriceHtml,
    pricePromoHtml as buildPricePromoHtml,
} from "@/utils/websiteCurrencyDisplay";
import { normalizeCatalogImageUrl } from "@/utils/catalogImageUrl";
import { readHorizontalSwipeDelta } from "@/utils/imageSwipe.js";
import { watchDesktopSiteViewport, isDesktopSiteOnMobile, isIOSDevice } from "@/utils/desktopSiteViewport.js";
import {
    isMobileNavContext,
    isLikelyPhoneDevice,
    watchMobileNavContext,
} from "@/utils/mobileNavContext.js";
import {
    catalogApiCategoryFromSlug,
    goalkeeperGloveBrandFromShopSlug,
    goalkeeperGlovesNavChildren,
    goalkeeperEquipmentNavChildren,
} from "@/constants/mobileNavTree.js";

export default {
    name: "GuantiDaPortierePage",
    data() {
        return {
            loading: true,
            catalogFetchInFlight: 0,
            errorMsg: "",
            products: [],
            facets: { brands: [], sizes: [], colors: [], attributes: [] },
            pagination: { page: 1, last_page: 1, per_page: 40, total: 0 },
            categoryTree: [],
            teamsportSubcategories: [],
            meta: {},
            sidebarOpenMainSlug: "",
            selectedTeamsportSub: "",
            q: "",
            selectedBrands: [],
            selectedSizes: [],
            selectedColors: [],
            selectedAttrs: [],
            sort: "newest",
            perPage: 40,
            viewMode: "grid",
            searchDebounceTimer: null,
            openFilterKey: "",
            showExtraFilters: false,
            requestSeq: 0,
            hoveredSku: null,
            cardImageIndex: {},
            cardImgActiveSku: null,
            _cardSwipe: null,
            _cardSwipeDragged: false,
            _cardSwipeDocCleanup: null,
            brandStripOverflow: false,
            brandStripCanScrollPrev: false,
            mobileSubcategoryOpen: false,
            mobileFilterHubOpen: false,
            mobileFilterDetailKey: "",
            mobileOverlayGuardUntil: 0,
            mobileDetailOpenedFromHub: false,
            /** عدد بطاقات وهمية أثناء التحميل الأول (شيمر) */
            plpSkeletonCount: 40,
            plpIsPhone: false,
            plpPhoneWideLayout: false,
            plpLayoutWidth: 0,
            _unwatchPlpViewport: null,
        };
    },
    computed: {
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        /** سحب صور البطاقة بالماوس فقط — على اللمس نترك التكبير بالقرص والنقر سريع */
        plpCardImageSwipeEnabled() {
            if (typeof window === "undefined" || typeof window.matchMedia !== "function") {
                return false;
            }
            return window.matchMedia("(hover: hover) and (pointer: fine)").matches;
        },
        plpShowCategoryTitle() {
            return this.plpShowSidebar;
        },
        /** شريط التصنيفات الجانبي — فوق 1000px على سطح المكتب فقط */
        plpShowSidebar() {
            return !this.plpIsPhone && this.plpLayoutWidth > 1000;
        },
        plpShowMobileSticky() {
            return this.plpIsPhone || this.plpLayoutWidth <= 1000;
        },
        plpMobileOverlayOpen() {
            return (
                this.mobileSubcategoryOpen ||
                this.mobileFilterHubOpen ||
                Boolean(this.mobileFilterDetailKey)
            );
        },
        lt() {
            const base = glovesPlpCopy(this.locale);
            return {
                ...base,
                scroll_prev: this.locale === "ar" ? "السابق" : "Previous",
                scroll_next: this.locale === "ar" ? "التالي" : "Next",
                categories_title: this.locale === "ar" ? "التصنيفات" : "Categories",
                mobile_navigation: base.mobile_navigation,
                filter_done: base.filter_done,
                close_menu: base.close_menu,
                sale_link: base.sale_link,
                subcategory: base.subcategory,
                price_filter: base.price_filter,
                price_unavailable: base.price_unavailable,
                filter_sheet: base.filter_sheet,
                show_products_caps: base.show_products_caps,
                facet_empty: base.facet_empty,
            };
        },
        showProductsCtaLabel() {
            const n = this.pagination.total ?? 0;
            const t = (this.lt.show_products_caps || "").replace(/\{count\}/g, String(n));
            if (this.locale === "ar") {
                return `✓ ${t}`;
            }
            return `✓ ${t.toUpperCase()}`;
        },
        currentParentMainForSub() {
            const cur = this.currentCatalogSlug;
            for (const m of this.sidebarMainItems) {
                if (this.catalogSlugEquals(m.slug, cur)) {
                    return m;
                }
                if ((m.children || []).some((ch) => this.childRouteMatches(ch, cur))) {
                    return m;
                }
            }
            const hinted = this.inferMainSectionFromShopSlug(cur);
            if (hinted) {
                const byHint = this.sidebarMainItems.find((x) => x.slug === hinted);
                if (byHint) {
                    return byHint;
                }
            }
            return this.sidebarMainItems[0] || null;
        },
        mobileCompactChips() {
            const chips = [];
            const sizes = this.facets.sizes || [];
            if (sizes.length) {
                chips.push({
                    key: "sizes",
                    label: this.locale === "ar" ? "المقاس (EU)" : "SHOE SIZE (EU)",
                });
            }
            if ((this.facets.brands || []).length) {
                chips.push({
                    key: "brand",
                    label: this.locale === "ar" ? this.lt.filter_brand : String(this.lt.filter_brand || "Brand").toUpperCase(),
                });
            }
            const hasColors = this.filterDropdowns.some((d) => d.key === "colors");
            if (hasColors) {
                chips.push({ key: "colors", label: this.lt.filter_color });
            } else {
                const attr = this.filterDropdowns.find((d) => d.kind === "attr");
                if (attr) {
                    chips.push({
                        key: attr.key,
                        label: attr.label,
                    });
                }
            }
            return chips.slice(0, 3);
        },
        selectedBrandSubtitle() {
            if (!this.selectedBrands.length) return "";
            const names = this.selectedBrands
                .map((id) => (this.facets.brands || []).find((x) => x.id === id)?.name)
                .filter(Boolean);
            return names.join(", ");
        },
        mobileHubRows() {
            const dash = this.lt.facet_empty || "—";
            const rows = [];
            rows.push({
                key: "sort",
                title: this.lt.sort_label,
                subtitle: this.sortTriggerLabel || dash,
                disabled: false,
            });
            const sizesLen = (this.facets.sizes || []).length;
            if (sizesLen) {
                rows.push({
                    key: "sizes",
                    title: this.locale === "ar" ? "المقاس (EU)" : "SHOE SIZE (EU)",
                    subtitle: this.selectedSizes?.length ? this.selectedSizes.join(", ") : dash,
                    disabled: false,
                });
            }
            rows.push({
                key: "subcategory",
                title: this.lt.subcategory,
                subtitle: (this.pageTitle || "").trim() || dash,
                disabled: false,
            });
            if ((this.facets.brands || []).length) {
                rows.push({
                    key: "brand",
                    title: this.lt.filter_brand,
                    subtitle: this.selectedBrandSubtitle || dash,
                    disabled: false,
                });
            }
            rows.push({
                key: "price",
                title: this.lt.price_filter,
                subtitle: this.lt.price_unavailable,
                disabled: true,
            });
            this.filterDropdowns.forEach((dd) => {
                if (dd.key === "sizes") {
                    return;
                }
                rows.push({
                    key: dd.key,
                    title: dd.label,
                    subtitle: this.hubSubtitleForDropdown(dd),
                    disabled: false,
                });
            });
            return rows;
        },
        mobileActiveFacetRow() {
            const k = this.mobileFilterDetailKey;
            if (!k || ["sort", "sizes", "colors", "brand", "subcategory", "price"].includes(k)) {
                return null;
            }
            return this.filterDropdowns.find((d) => d.key === k) || null;
        },
        mobileFilterDetailTitle() {
            const k = this.mobileFilterDetailKey;
            if (k === "sort") return this.lt.sort_label;
            if (k === "brand") return this.lt.filter_brand;
            if (k === "sizes") {
                return this.locale === "ar" ? "المقاس (EU)" : "SHOE SIZE (EU)";
            }
            if (k === "colors") return this.lt.filter_color;
            if (k === "subcategory") return this.lt.subcategory;
            if (k === "price") return this.lt.price_filter;
            const row = this.mobileActiveFacetRow;
            return row?.label || k;
        },
        sortTriggerLabel() {
            const map = {
                relevance: this.lt.sort_popular,
                newest: this.lt.sort_newest,
                price_desc: this.lt.sort_price_down,
                price_asc: this.lt.sort_price_up,
                discount_desc: this.lt.sort_discount_down,
                name_asc: this.lt.sort_name_asc,
                name_desc: this.lt.sort_name_desc,
            };
            return map[this.sort] || this.lt.sort_popular;
        },
        productsCountText() {
            const n = this.pagination.total ?? 0;
            return (this.lt.products_found || "").replace("{count}", String(n));
        },
        /** نافذة أرقام صفحات مثل keepersport.net (حتى 7 أرقام متجاورة). */
        pagerWindowedPages() {
            const last = Math.max(1, parseInt(String(this.pagination.last_page || 1), 10) || 1);
            const curRaw = parseInt(String(this.pagination.page || 1), 10) || 1;
            const cur = Math.min(last, Math.max(1, curRaw));
            const maxWindow = 7;
            const w = Math.min(maxWindow, last);
            let start = Math.max(1, cur - Math.floor(w / 2));
            let end = Math.min(last, start + w - 1);
            start = Math.max(1, end - w + 1);
            const pages = [];
            for (let p = start; p <= end; p += 1) {
                pages.push(p);
            }
            return pages;
        },
        isSearchPage() {
            return (
                this.$route.name === "catalog-search"
                || /\/search\/?$/i.test(String(this.$route.path || ""))
            );
        },
        /** للـ API: بحث الموقع بدون تقييد تصنيف؛ أوراق الماركة → goalkeeper-gloves */
        catalogCategoryForApi() {
            if (this.isSearchPage) {
                return "";
            }
            return catalogApiCategoryFromSlug(this.currentCatalogSlug);
        },
        currentCatalogSlug() {
            if (this.isSearchPage) {
                return "search";
            }
            const path = String(this.$route.path || "");
            const bootParam = this.$route.params.bootCategory;
            if (bootParam && /^football-boots-/i.test(bootParam)) {
                const leaf = String(bootParam)
                    .replace(/^football-boots-/i, "")
                    .replace(/_/g, "-")
                    .toLowerCase();
                return leaf || "football-boots";
            }
            const shopMatch = path.match(/\/shop\/([^/?#]+)/i);
            if (shopMatch?.[1]) {
                return String(shopMatch[1]).replace(/_/g, "-").toLowerCase();
            }
            const raw = this.$route.params.catalogSlug || this.$route.params.id || "goalkeeper-gloves";
            return String(raw).replace(/_/g, "-").toLowerCase();
        },
        sidebarMainItems() {
            const roots = this.categoryTree || [];
            const defs = [
                { slug: "goalkeeper-gloves", fallbackLabel: "Goalkeeper Gloves", aliases: ["goalkeeper-gloves", "guanti-da-portiere", "torwarthandschuhe"] },
                { slug: "football-boots", fallbackLabel: "Football Boots", aliases: ["football-boots", "scarpe-da-calcio", "fussballschuhe"] },
                { slug: "goalkeeper-apparel", fallbackLabel: "Goalkeeper apparel", aliases: ["goalkeeper-apparel", "abbigliamento-da-portiere", "torwartbekleidung"] },
                { slug: "goalkeeper-equipment", fallbackLabel: "Goalkeeper equipment", aliases: ["goalkeeper-equipment", "equipaggiamento-per-portiere", "torwartausruestung"] },
                { slug: "teamsport", fallbackLabel: "Teamsport", aliases: ["teamsport", "abbigliamento", "teamwear"] },
            ];

            const tsSubs = this.teamsportSubcategories || [];

            return defs.map((def) => {
                if (def.slug === "teamsport" && tsSubs.length) {
                    const navTs = this.meta?.shop_nav_leaf_counts?.teamsport;
                    const hasTsNav =
                        navTs && typeof navTs === "object" && Object.keys(navTs).length > 0;
                    const subsFiltered = hasTsNav
                        ? tsSubs.filter((sub) => {
                              const hasParentKey = Object.prototype.hasOwnProperty.call(navTs, sub.slug);
                              const n = hasParentKey ? Number(navTs[sub.slug]) : NaN;
                              if (Number.isFinite(n) && n > 0) {
                                  return true;
                              }
                              return (sub.children || []).some((gc) => {
                                  const k = `${sub.slug}/${gc.slug}`;
                                  if (!Object.prototype.hasOwnProperty.call(navTs, k)) {
                                      return false;
                                  }
                                  const m = Number(navTs[k]);
                                  return Number.isFinite(m) && m > 0;
                              });
                          })
                        : tsSubs;
                    return {
                        slug: def.slug,
                        label: def.fallbackLabel,
                        children: subsFiltered.map((sub) => ({
                            id: sub.slug,
                            name: sub.label,
                            slug: "teamsport/" + sub.slug,
                            filterIds: sub.ids,
                            children: (sub.children || []).filter((gc) => {
                                if (!hasTsNav) {
                                    return true;
                                }
                                const k = `${sub.slug}/${gc.slug}`;
                                if (!Object.prototype.hasOwnProperty.call(navTs, k)) {
                                    return false;
                                }
                                const m = Number(navTs[k]);
                                return Number.isFinite(m) && m > 0;
                            }),
                        })),
                    };
                }

                if (def.slug === "goalkeeper-apparel") {
                    const exactGk = roots.find((r) => {
                        const s = String(r.slug || "").toLowerCase();
                        return def.aliases.some((a) => s === a);
                    });
                    return {
                        slug: def.slug,
                        label: exactGk?.name || def.fallbackLabel,
                        children: this.goalkeeperApparelSidebarChildrenForNav(),
                    };
                }

                if (def.slug === "goalkeeper-gloves") {
                    const exactGk = roots.find((r) => {
                        const s = String(r.slug || "").toLowerCase();
                        return def.aliases.some((a) => s === a);
                    });
                    return {
                        slug: def.slug,
                        label: exactGk?.name || def.fallbackLabel,
                        children: this.goalkeeperGlovesSidebarChildrenForNav(),
                    };
                }

                if (def.slug === "goalkeeper-equipment") {
                    const exactGk = roots.find((r) => {
                        const s = String(r.slug || "").toLowerCase();
                        return def.aliases.some((a) => s === a);
                    });
                    return {
                        slug: def.slug,
                        label: exactGk?.name || def.fallbackLabel,
                        children: this.goalkeeperEquipmentSidebarChildrenForNav(),
                    };
                }

                const exact = roots.find((r) => {
                    const s = String(r.slug || "").toLowerCase();
                    return def.aliases.some((a) => s === a);
                });

                const merged = this.collectSectionChildren(def.slug, roots);
                const rawChildren =
                    merged.length > 0 ? merged : exact?.children?.length ? exact.children : this.legacySidebarChildren(def, roots);

                return {
                    slug: def.slug,
                    label: exact?.name || def.fallbackLabel,
                    children: this.filterShopNavChildren(rawChildren || [], def.slug),
                };
            });
        },
        pageTitle() {
            if (this.isSearchPage) {
                const q = String(this.q || this.$route.query?.q || "").trim();
                if (q) {
                    return (this.lt.search_results_for || "").replace(/\{q\}/g, q);
                }
                return this.lt.search_title || (this.pageLang === "ar" ? "بحث" : "Search");
            }
            const slug = this.currentCatalogSlug;
            if (slug === "sale" || slug === "saldi") {
                return this.lt.sale_link || (this.pageLang === "ar" ? "تخفيضات" : "Sale");
            }
            const main = this.sidebarMainItems.find((m) => m.slug === slug);
            if (main) return main.label;
            for (const m of this.sidebarMainItems) {
                for (const ch of m.children || []) {
                    if (this.childRouteMatches(ch, slug)) {
                        return ch.name || ch.slug || this.lt.title;
                    }
                }
            }
            for (const c of this.categoryTree || []) {
                if (c.slug === slug || c.id === slug) return c.name;
                for (const ch of c.children || []) {
                    if (ch.slug === slug || ch.id === slug) return ch.name;
                }
            }
            return this.lt.title;
        },
        metaHint() {
            const s = this.meta?.source_product_count;
            const a = this.meta?.after_category_count;
            if (s == null) return "";
            return this.locale === "ar"
                ? `المصدر: ${embedWesternDigits(s)} منتج | بعد التصنيف: ${embedWesternDigits(a)}`
                : `Catalog: ${s} products | in category: ${a}`;
        },
        filterDropdowns() {
            const rows = [];
            const attrs = this.facets.attributes || [];
            attrs.forEach((attr) => {
                rows.push({
                    key: "attr:" + attr.key,
                    kind: "attr",
                    attrKey: attr.key,
                    label: attr.label || attr.key,
                    options: (attr.values || []).map((v) => ({
                        value: v.key,
                        label: v.label || v.key,
                        count: v.count != null ? v.count : null,
                    })),
                });
            });
            if ((this.facets.sizes || []).length) {
                rows.push({
                    key: "sizes",
                    kind: "size",
                    label: this.locale === "ar" ? "المقاس" : "Size",
                    options: (this.facets.sizes || []).map((sz) => {
                        const n = this.normalizeSizeFacet(sz);
                        return { value: n.value, label: n.label, count: n.count };
                    }),
                });
            }
            const colorOpts = (this.facets.colors || [])
                .map((c) => ({
                    value: this.colorToken(c),
                    label: c.name || c.hex || "",
                    count: c.count != null ? c.count : null,
                }))
                .filter((o) => o.value && o.label);
            if (colorOpts.length) {
                rows.push({
                    key: "colors",
                    kind: "color",
                    label: this.locale === "ar" ? "اللون" : "Color",
                    options: colorOpts,
                });
            }
            return rows;
        },
        quickFilterDropdowns() {
            const rows = this.filterDropdowns || [];
            return rows.slice(0, 5);
        },
        extraFilterDropdowns() {
            const rows = this.filterDropdowns || [];
            return rows.slice(5);
        },
        activeFilterCount() {
            let total = 0;
            total += this.selectedBrands.length;
            total += this.selectedColors.length;
            total += this.selectedSizes.length;
            total += this.selectedAttrs.length;
            return total;
        },
    },
    watch: {
        currentCatalogSlug() {
            if (this.isSearchPage) {
                this.applySearchFromRoute();
            } else {
                this.resetFiltersForCategoryChange();
            }
            this.products = [];
            this.preflightSidebarOpenForRoute();
            this.$nextTick(() => {
                this.fetchProducts(1);
                this.syncSidebarOpenState();
            });
        },
        categoryTree() {
            this.$nextTick(() => this.syncSidebarOpenState());
        },
        "$route.path"() {
            this.$nextTick(() => this.syncSidebarOpenState());
        },
        "$route.query.teamsport_sub"(sub) {
            if (this.currentCatalogSlug !== "teamsport") {
                return;
            }
            this.selectedTeamsportSub = sub ? String(sub) : "";
            this.fetchProducts(1);
        },
        "$route.query.brand"() {
            if (this.isSearchPage) {
                return;
            }
            if (this.$route.query?.brands != null) {
                return;
            }
            const next = this.resolveBrandFromRoute();
            if (next) {
                const normalized = this.normalizeBrandFilterId(next);
                if (
                    this.selectedBrands.length !== 1
                    || this.normalizeBrandFilterId(this.selectedBrands[0]) !== normalized
                ) {
                    this.selectedBrands = [normalized];
                    this.fetchProducts(1);
                }
                return;
            }
            // لا تمسح اختيار multi-brand إذا كان ما زال مفعّلاً محلياً أثناء انتقال الرابط
            if (this.selectedBrands.length > 1) {
                return;
            }
            if (this.selectedBrands.length) {
                this.selectedBrands = [];
                this.fetchProducts(1);
            }
        },
        "$route.query.brands"() {
            if (this.isSearchPage) {
                return;
            }
            this.syncBrandsFromRoute();
            this.fetchProducts(1);
        },
        "$route.query.q"(q) {
            const next = typeof q === "string" ? q.trim() : "";
            if (next === String(this.q || "").trim()) {
                return;
            }
            this.q = next;
            this.fetchProducts(1);
        },
        "facets.brands": {
            handler() {
                this.$nextTick(() => {
                    this.syncBrandStripScrollOrigin();
                    this.updateBrandStripOverflow();
                });
            },
            deep: true,
        },
        mobileSubcategoryOpen: "syncMobileBodyScroll",
        mobileFilterHubOpen: "syncMobileBodyScroll",
        mobileFilterDetailKey: "syncMobileBodyScroll",
    },
    created() {
        this.syncPlpViewportMode();
    },
    async mounted() {
        this.applyTeamsportSubFromRoute();
        this.syncBrandsFromRoute();
        this.applySearchFromRoute();
        await this.fetchProducts(1, { bootstrap: true });
        this.syncSidebarOpenState();
        document.addEventListener("click", this.handleOutsideClick);
        document.addEventListener("keydown", this.handleMobileSheetEscape);
        window.addEventListener("resize", this.updateBrandStripOverflow);
        this.syncPlpViewportMode();
        const onPlpViewportChange = () => this.syncPlpViewportMode();
        const unwatchMobile = watchMobileNavContext(onPlpViewportChange);
        const unwatchDesktop = watchDesktopSiteViewport(onPlpViewportChange);
        window.addEventListener("resize", onPlpViewportChange, { passive: true });
        window.addEventListener("orientationchange", onPlpViewportChange, { passive: true });
        if (typeof MutationObserver !== "undefined" && document.documentElement) {
            this._plpHtmlClassObs = new MutationObserver(onPlpViewportChange);
            this._plpHtmlClassObs.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ["class"],
            });
        }
        this._unwatchPlpViewport = () => {
            unwatchMobile();
            unwatchDesktop();
            window.removeEventListener("resize", onPlpViewportChange);
            window.removeEventListener("orientationchange", onPlpViewportChange);
            if (this._plpHtmlClassObs) {
                this._plpHtmlClassObs.disconnect();
                this._plpHtmlClassObs = null;
            }
        };
        this.$nextTick(() => {
            this.syncPlpViewportMode();
            requestAnimationFrame(() => this.syncPlpViewportMode());
            this.syncBrandStripScrollOrigin();
            this.updateBrandStripOverflow();
        });
        setTimeout(() => this.syncPlpViewportMode(), 120);
    },
    beforeUnmount() {
        this.detachCardImgDocSwipe();
        if (this._unwatchPlpViewport) {
            this._unwatchPlpViewport();
            this._unwatchPlpViewport = null;
        }
        document.removeEventListener("click", this.handleOutsideClick);
        document.removeEventListener("keydown", this.handleMobileSheetEscape);
        window.removeEventListener("resize", this.updateBrandStripOverflow);
        document.body.style.overflow = "";
        document.documentElement.classList.remove("ks-plp-mob-overlay-open");
    },
    methods: {
        syncPlpViewportMode() {
            const root =
                typeof document !== "undefined" ? document.documentElement : null;
            const touchNavClass = Boolean(root?.classList.contains("ks-touch-nav"));
            const desktopSiteClass = Boolean(
                root?.classList.contains("ks-desktop-site-mobile")
            );
            const desktopSite =
                isDesktopSiteOnMobile() || desktopSiteClass;
            const iosDesktopSite =
                typeof window !== "undefined" &&
                isIOSDevice() &&
                (window.innerWidth || 0) >= 768;
            const phone =
                !desktopSite &&
                (isLikelyPhoneDevice() ||
                    isMobileNavContext() ||
                    touchNavClass ||
                    iosDesktopSite);
            const layoutW =
                typeof window !== "undefined"
                    ? window.innerWidth || document.documentElement?.clientWidth || 0
                    : 0;
            this.plpIsPhone = phone;
            this.plpLayoutWidth = layoutW;
            this.plpPhoneWideLayout = phone && !desktopSite && layoutW >= 556;
        },
        /** مطابقة مسارات التصنيف: /shop/cleats vs slug من الـ API (حالة الأحرف، شرطة/underscore). */
        normalizeCatalogSlugToken(value) {
            if (value == null || value === "") {
                return "";
            }
            return String(value).replace(/_/g, "-").trim().toLowerCase();
        },
        applyTeamsportSubFromRoute() {
            if (this.currentCatalogSlug !== "teamsport") {
                return;
            }
            const sub = this.$route.query?.teamsport_sub;
            if (typeof sub === "string" && sub.trim()) {
                this.selectedTeamsportSub = sub.trim();
            }
        },
        /** ماركة من slug المسار أولاً (مثل erima-goalkeeper-gloves)، ثم ?brand= */
        resolveBrandFromRoute() {
            const slugBrand = goalkeeperGloveBrandFromShopSlug(this.currentCatalogSlug);
            if (slugBrand) {
                return slugBrand;
            }
            const raw = this.$route.query?.brand;
            return typeof raw === "string" ? raw.trim() : "";
        },
        syncBrandFromRoute() {
            const brand = this.resolveBrandFromRoute();
            this.selectedBrands = brand ? [this.normalizeBrandFilterId(brand)] : [];
        },
        parseBrandsFromRouteQuery() {
            const raw = this.$route.query?.brands;
            if (raw == null) {
                return null;
            }
            if (typeof raw !== "string") {
                return [];
            }
            const trimmed = raw.trim();
            if (!trimmed) {
                return [];
            }
            return trimmed
                .split(",")
                .map((part) => this.normalizeBrandFilterId(part.trim()))
                .filter(Boolean);
        },
        syncBrandsFromRoute() {
            const fromQuery = this.parseBrandsFromRouteQuery();
            if (fromQuery !== null) {
                this.selectedBrands = fromQuery;
                return;
            }
            this.syncBrandFromRoute();
        },
        normalizeBrandFilterId(value) {
            const raw = String(value || "").trim();
            if (!raw) {
                return "";
            }
            const lower = raw.toLowerCase();
            const fromFacet = (this.facets.brands || []).find((b) => {
                const id = String(b.id || "").trim().toLowerCase();
                const name = String(b.name || "").trim().toLowerCase();
                return id === lower || name === lower;
            });
            return fromFacet ? String(fromFacet.id) : raw;
        },
        /** صفحة ماركة واحدة: /shop/{brand}-goalkeeper-gloves */
        isGoalkeeperGloveBrandLeafRoute() {
            if (this.isSearchPage) {
                return false;
            }
            return Boolean(goalkeeperGloveBrandFromShopSlug(this.currentCatalogSlug));
        },
        generalGoalkeeperGlovesPath() {
            return `/${this.locale}/goalkeeper-gloves`;
        },
        nextSelectedBrandsAfterToggle(brandId) {
            const sid = this.normalizeBrandFilterId(brandId);
            const next = this.selectedBrands.map((b) => this.normalizeBrandFilterId(b));
            const i = next.indexOf(sid);
            if (i >= 0) {
                next.splice(i, 1);
                return next;
            }
            if (this.isGoalkeeperGloveBrandLeafRoute()) {
                const routeBrand = goalkeeperGloveBrandFromShopSlug(this.currentCatalogSlug);
                if (routeBrand) {
                    const normalizedRouteBrand = this.normalizeBrandFilterId(routeBrand);
                    const ri = next.indexOf(normalizedRouteBrand);
                    if (ri >= 0) {
                        next.splice(ri, 1);
                    }
                }
            }
            next.push(sid);
            return next;
        },
        isBrandAttributeFacet(row) {
            if (!row || row.kind !== "attr") {
                return false;
            }
            const key = String(row.attrKey || "").trim().toLowerCase();
            const label = String(row.label || "").trim().toLowerCase();
            return key === "brand" || key === "marke" || label === "brand" || label === "marke";
        },
        isBrandFilterValueSelected(row, opt) {
            const sid = this.normalizeBrandFilterId(opt.value);
            if (this.selectedBrands.some((b) => this.normalizeBrandFilterId(b) === sid)) {
                return true;
            }
            return this.selectedAttrs.includes(this.pairForAttr(row.attrKey, opt.value));
        },
        stripBrandAttributeFilters() {
            this.selectedAttrs = this.selectedAttrs.filter((pair) => {
                const ak = String(pair.split("|")[0] || "").trim().toLowerCase();
                return ak !== "brand" && ak !== "marke";
            });
        },
        applyBrandFilterToggle(brandId) {
            const next = this.nextSelectedBrandsAfterToggle(brandId);
            this.stripBrandAttributeFilters();
            // كل التصنيفات: الماركات المختارة تظهر في الرابط (?brands=) للتسويق والمشاركة
            this.navigateWithSelectedBrands(next);
        },
        /**
         * يحدّث ?brands= في الرابط.
         * صفحات ماركة القفازات (/shop/{brand}-goalkeeper-gloves) تنتقل إلى /goalkeeper-gloves?brands=
         * باقي التصنيفات تبقى على نفس المسار (مثل /football-boots?brands=adidas,reusch)
         */
        navigateWithSelectedBrands(brands) {
            const normalized = (Array.isArray(brands) ? brands : [])
                .map((b) => this.normalizeBrandFilterId(b))
                .filter(Boolean);
            const path = this.isGoalkeeperGloveBrandLeafRoute()
                ? this.generalGoalkeeperGlovesPath()
                : this.$route.path;
            const query = { ...this.$route.query };
            delete query.brand;
            if (normalized.length) {
                query.brands = normalized.join(",");
            } else {
                delete query.brands;
            }
            const currentPath = this.$route.path;
            const currentBrands = this.parseBrandsFromRouteQuery();
            const samePath = currentPath === path;
            const sameBrands =
                Array.isArray(currentBrands)
                && currentBrands.length === normalized.length
                && normalized.every((id, i) => this.normalizeBrandFilterId(currentBrands[i]) === id);
            if (samePath && sameBrands && this.$route.query?.brand == null) {
                this.selectedBrands = normalized;
                this.applyFilters();
                return;
            }
            this.$router.push({ path, query });
        },
        /** توافق مع الاستدعاءات القديمة */
        navigateGoalkeeperGlovesWithBrands(brands) {
            this.navigateWithSelectedBrands(brands);
        },
        applySearchFromRoute() {
            const raw = this.$route.query?.q;
            this.q = typeof raw === "string" ? raw.trim() : "";
        },
        /** مسار PLP لفرع الأحذية كما على keepersport.net: /locale/football-boots-hard-court */
        plpPathForShopChild(mainSlug, ch) {
            const loc = String(this.locale || "en");
            if (!ch || ch.filterIds) {
                return `/${loc}/shop/${ch?.slug || ""}`;
            }
            if (mainSlug === "goalkeeper-gloves" && ch.queryBrand) {
                return `/${loc}/goalkeeper-gloves?brand=${encodeURIComponent(String(ch.queryBrand))}`;
            }
            const raw = String(ch.slug || ch.id || "").trim();
            if (!raw) {
                return `/${loc}/shop/`;
            }
            const slug = raw.replace(/_/g, "-").toLowerCase();
            if (mainSlug === "goalkeeper-gloves" && slug.endsWith("-goalkeeper-gloves")) {
                return `/${loc}/shop/${slug}`;
            }
            if (mainSlug === "goalkeeper-equipment") {
                return `/${loc}/shop/${slug}`;
            }
            if (
                mainSlug === "football-boots" &&
                slug &&
                slug !== "football-boots" &&
                !slug.includes("/") &&
                !["scarpe-da-calcio", "fussballschuhe"].includes(slug)
            ) {
                return `/${loc}/football-boots-${slug}`;
            }
            return `/${loc}/shop/${raw}`;
        },
        catalogSlugEquals(a, b) {
            return this.normalizeCatalogSlugToken(a) === this.normalizeCatalogSlugToken(b);
        },
        /**
         * يطابق slug المسار مع عنصر القائمة: مساواة، أو ذيل بعد /، أو آخر جزء بعد - (مثل fussballschuh-cleats ↔ cleats).
         */
        childRouteMatches(ch, current) {
            let cur = this.normalizeCatalogSlugToken(current);
            if (!cur) {
                return false;
            }
            if (cur.startsWith("football-boots-")) {
                cur = this.normalizeCatalogSlugToken(cur.replace(/^football-boots-/i, ""));
            }
            const raws = [ch?.slug, ch?.id, ch?.key].filter((x) => x != null && String(x).trim() !== "");
            for (const raw of raws) {
                const full = this.normalizeCatalogSlugToken(raw);
                if (!full) {
                    continue;
                }
                if (full === cur) {
                    return true;
                }
                if (full.endsWith("/" + cur)) {
                    return true;
                }
                if (full.includes("/")) {
                    const slashTail = full.split("/").pop() || "";
                    if (slashTail && this.catalogSlugEquals(slashTail, cur)) {
                        return true;
                    }
                }
                const hyphenParts = full.split("-");
                const hyphenTail = hyphenParts.length ? hyphenParts[hyphenParts.length - 1] : "";
                if (hyphenTail && this.catalogSlugEquals(hyphenTail, cur)) {
                    return true;
                }
            }
            return false;
        },
        /**
         * عندما لا يُوجد الـ slug في شجرة الـ API بعد، نربط أشهر مسارات /shop/* بالقسم الرئيسي.
         *
         * @return string slug رئيسي أو فارغ
         */
        inferMainSectionFromShopSlug(slug) {
            const s = this.normalizeCatalogSlugToken(slug);
            if (s.startsWith("football-boots-")) {
                return "football-boots";
            }
            const bootish = new Set([
                "cleats",
                "firm-ground",
                "soft-ground",
                "artificial-grass",
                "turf",
                "indoor",
                "sandals",
                "hard-court",
                "hardcourt",
                "street",
                "multi-ground",
                "football-boots",
                "scarpe-da-calcio",
                "fussballschuhe",
                "fg",
                "sg",
                "ag",
                "tf",
                "mg",
                "ic",
                "in",
                "fxg",
                "astro-turf",
                "accessories",
                "kids-football-boots",
                "leisure-kids",
            ]);
            if (bootish.has(s)) {
                return "football-boots";
            }
            const gkApparelLeaves = new Set([
                "goalkeeper-underwear",
                "goalkeeper-pants",
                "goalkeeper-jerseys",
                "goalkeeper-set",
                "goalkeeper-socks",
                "goalkeeper-training-tops",
                "goalkeeper-undershirts",
                "goalkeeper-undershorts",
            ]);
            if (gkApparelLeaves.has(s)) {
                return "goalkeeper-apparel";
            }
            if (s.endsWith("-goalkeeper-gloves") || s === "packs-sets-goalkeeper-gloves") {
                return "goalkeeper-gloves";
            }
            for (const ch of goalkeeperGlovesNavChildren(false)) {
                if (this.childRouteMatches(ch, s)) {
                    return "goalkeeper-gloves";
                }
            }
            for (const m of this.sidebarMainItems) {
                if (m.slug === "football-boots") {
                    for (const ch of m.children || []) {
                        for (const raw of [ch.slug, ch.id]) {
                            const full = this.normalizeCatalogSlugToken(raw);
                            if (!full) {
                                continue;
                            }
                            if (full === s) {
                                return "football-boots";
                            }
                            const tail = full.includes("/") ? full.split("/").pop() || "" : full.split("-").pop() || "";
                            if (tail && this.catalogSlugEquals(tail, s)) {
                                return "football-boots";
                            }
                        }
                    }
                }
            }
            for (const sub of this.teamsportSubcategories || []) {
                const ch = { slug: "teamsport/" + sub.slug, id: "teamsport/" + sub.slug };
                if (this.childRouteMatches(ch, s)) {
                    return "teamsport";
                }
                for (const gc of sub.children || []) {
                    const ch2 = { slug: `teamsport/${sub.slug}/${gc.slug}` };
                    if (this.childRouteMatches(ch2, s)) {
                        return "teamsport";
                    }
                }
            }
            for (const ch of goalkeeperEquipmentNavChildren(false)) {
                if (this.childRouteMatches(ch, s)) {
                    return "goalkeeper-equipment";
                }
                for (const gc of ch.children || []) {
                    if (this.childRouteMatches(gc, s)) {
                        return "goalkeeper-equipment";
                    }
                }
            }
            return "";
        },
        /** فروع ملابس الحارس الظاهرة في القائمة: نخفي الأوراق ذات العدد صفر عند توفر أعداد من الـ API. */
        goalkeeperApparelSidebarChildrenForNav() {
            const rows = this.curatedGoalkeeperApparelSidebarChildren();
            const counts = this.meta?.gk_apparel_leaf_counts;
            if (!counts || typeof counts !== "object") {
                return rows;
            }
            const keys = Object.keys(counts);
            if (!keys.length) {
                return rows;
            }
            return rows.filter((ch) => {
                if (this.childRouteMatches(ch, this.currentCatalogSlug)) {
                    return true;
                }
                const slug = ch?.slug;
                if (!slug) {
                    return false;
                }
                if (!Object.prototype.hasOwnProperty.call(counts, slug)) {
                    return false;
                }
                const n = Number(counts[slug]);
                return Number.isFinite(n) && n > 0;
            });
        },
        /** قائمة قفازات الحارس — نفس keepersport.net */
        goalkeeperGlovesSidebarChildrenForNav() {
            return goalkeeperGlovesNavChildren(this.pageLang === "ar");
        },
        /** معدات الحارس — نفس keepersport.net */
        goalkeeperEquipmentSidebarChildrenForNav() {
            return goalkeeperEquipmentNavChildren(this.pageLang === "ar");
        },
        /** ترتيب وتسمية أوراق ملابس الحارس كما على keepersport.net (بدون دمج عشوائي من شجرة الـ API). */
        curatedGoalkeeperApparelSidebarChildren() {
            const ar = this.pageLang === "ar";
            return [
                {
                    id: "gk-underwear",
                    slug: "goalkeeper-underwear",
                    name: ar ? "ملابس داخلية لحارس المرمى" : "Goalkeeper underwear",
                },
                {
                    id: "gk-pants",
                    slug: "goalkeeper-pants",
                    name: ar ? "بناطيل حارس المرمى" : "Goalkeeper pants",
                },
                {
                    id: "gk-jerseys",
                    slug: "goalkeeper-jerseys",
                    name: ar ? "قمصان حارس المرمى" : "Goalkeeper jerseys",
                },
                {
                    id: "gk-set",
                    slug: "goalkeeper-set",
                    name: ar ? "طقم حارس المرمى" : "Goalkeeper set",
                },
                {
                    id: "gk-training",
                    slug: "goalkeeper-training-tops",
                    name: ar ? "قمصان تدريب حارس المرمى" : "Goalkeeper training tops",
                },
                {
                    id: "gk-socks",
                    slug: "goalkeeper-socks",
                    name: ar ? "جوارب حارس المرمى" : "Goalkeeper socks",
                },
            ];
        },
        collectSectionChildren(sectionSlug, roots) {
            const list = roots || [];
            const seen = new Set();
            const out = [];
            for (const r of list) {
                if (!this.rootBelongsToSection(sectionSlug, r)) continue;
                const kids = r.children || [];
                if (kids.length) {
                    for (const ch of kids) {
                        const key = String(ch.id || ch.slug || "");
                        if (!key || seen.has(key)) continue;
                        seen.add(key);
                        out.push(ch);
                    }
                } else {
                    const key = `root:${String(r.slug || r.id || "")}`;
                    if (!key || seen.has(key)) continue;
                    seen.add(key);
                    out.push({
                        id: String(r.slug || r.id || ""),
                        name: r.name || r.slug || "",
                        slug: String(r.slug || r.id || ""),
                    });
                }
            }
            out.sort((a, b) =>
                String(a.name || "").localeCompare(String(b.name || ""), undefined, { sensitivity: "base" })
            );
            return out;
        },
        legacySidebarChildren(def, roots) {
            const r = roots.find((root) => {
                const s = String(root.slug || "").toLowerCase();
                const n = String(root.name || "").toLowerCase();
                return def.aliases.some((a) => s === a || n.includes(a.replace(/-/g, " ")));
            });
            return r?.children || [];
        },
        rootBelongsToSection(sectionSlug, root) {
            const s = String(root.slug || "").toLowerCase();
            const n = String(root.name || "").toLowerCase();
            const hay = `${s} ${n}`;

            const teamsportOnlyRootSlugs = [
                "jerseys",
                "shorts",
                "tracksuits",
                "footballs",
                "polo-shirts",
                "training-tops",
                "tank-tops",
                "functional-socks",
                "knee-high-socks",
                "boxer-shorts",
                "team-bag",
                "sport-bras",
                "underpants",
                "long-pants",
                "short-pants",
                "short-sleeve",
                "socks",
            ];
            if (teamsportOnlyRootSlugs.includes(s) && !hay.includes("goalkeeper") && !hay.includes("torwart")) {
                return sectionSlug === "teamsport";
            }

            switch (sectionSlug) {
                case "goalkeeper-gloves":
                    if (teamsportOnlyRootSlugs.includes(s)) return false;
                    return (
                        ["goalkeeper-gloves", "guanti-da-portiere", "torwarthandschuhe"].includes(s) ||
                        (/(glove|guanti|handschuh|handschuhe)/i.test(hay) &&
                            !/functional sock|knee-high sock/i.test(hay))
                    );
                case "football-boots":
                    if (/glove|handschuh|guanti/i.test(hay) && !/boot|schuh|scarpe/i.test(hay)) return false;
                    return (
                        ["football-boots", "scarpe-da-calcio", "fussballschuhe"].includes(s) ||
                        /fussballschuh|football boot|scarpe da calcio|football boots/i.test(hay)
                    );
                case "goalkeeper-apparel":
                    if (["goalkeeper-apparel", "abbigliamento-da-portiere", "torwartbekleidung"].includes(s)) return true;
                    if (teamsportOnlyRootSlugs.includes(s) && !hay.includes("goalkeeper") && !hay.includes("torwart")) {
                        return false;
                    }
                    return (
                        /goalkeeper.*(jersey|pant|sock|under|training|shirt|short|tight|bib|set|undershirt|undershort)/i.test(
                            hay
                        ) ||
                        /torwart.*(bekleidung|shirt|hose|sock|short|trikot|jersey|training)/i.test(hay) ||
                        /abbigliamento.*portiere|portiere.*(maglia|pant|calz)/i.test(hay)
                    );
                case "goalkeeper-equipment": {
                    if (teamsportOnlyRootSlugs.includes(s) && !hay.includes("goalkeeper") && !hay.includes("torwart")) {
                        return false;
                    }
                    if (/field player glove|spielerhandschuh|feldspieler/i.test(hay)) {
                        return false;
                    }
                    const equipmentRoots = new Set([
                        "goalkeeper-equipment",
                        "equipaggiamento-per-portiere",
                        "torwartausruestung",
                        "gk-bag",
                        "goalkeeper-set",
                        "training-aids",
                        "care",
                        "glove-care",
                        "rehabilitation",
                    ]);
                    if (equipmentRoots.has(s)) {
                        return true;
                    }
                    return (
                        /goalkeeper.*(bag|ball|set|equipment|training|cap|shin|water|bottle|pump|hurdle|sponge|towel|strap|holder|care|grip|accessory|accessories)/i.test(
                            hay
                        ) ||
                        /torwart.*(tasche|ball|set|trainings|hilfe|ausrust|ruestung|equipment|zubeh)/i.test(hay) ||
                        /\bgk[-\s]?bag\b/i.test(hay)
                    );
                }
                case "teamsport":
                    return (
                        hay.includes("teamsport") ||
                        hay.includes("teamwear") ||
                        (teamsportOnlyRootSlugs.includes(s) && !hay.includes("goalkeeper") && !hay.includes("torwart"))
                    );
                default:
                    return false;
            }
        },
        syncSidebarOpenState() {
            const current = this.currentCatalogSlug;
            const mains = this.sidebarMainItems;

            const mainHit = mains.find((m) => this.catalogSlugEquals(m.slug, current));
            if (mainHit) {
                this.sidebarOpenMainSlug = mainHit.slug;
                return;
            }

            const parent = mains.find((m) =>
                (m.children || []).some((ch) => this.childRouteMatches(ch, current))
            );
            const hinted = this.inferMainSectionFromShopSlug(current);
            const hintedOk = hinted && mains.some((m) => m.slug === hinted) ? hinted : "";

            let next = parent?.slug || hintedOk || "";

            if (!next && this.sidebarOpenMainSlug) {
                const prev = mains.find((m) => m.slug === this.sidebarOpenMainSlug);
                if (prev?.children?.some((ch) => this.childRouteMatches(ch, current))) {
                    next = prev.slug;
                }
            }

            if (!next && (current.startsWith("teamsport") || current.includes("teamwear"))) {
                if (mains.some((m) => m.slug === "teamsport")) {
                    next = "teamsport";
                }
            }

            this.sidebarOpenMainSlug = next || "";
        },
        /** يظهر الفروع إذا فُتح القسم يدوياً أو إذا كان المسار الحالي تحت هذا القسم (حتى لا يُغلق الأكورديون بعد التنقل). */
        desktopShowCategoryChildren(main) {
            if (!main?.children?.length) {
                return false;
            }
            return this.sidebarOpenMainSlug === main.slug || this.isMainActive(main.slug);
        },
        /**
         * يخفي فروع القائمة ذات العدد 0 حسب meta.shop_nav_leaf_counts (نفس منطق جلب المنتجات في الخادم).
         */
        filterShopNavChildren(children, mainSlug) {
            if (
                mainSlug === "goalkeeper-apparel" ||
                mainSlug === "goalkeeper-gloves" ||
                mainSlug === "goalkeeper-equipment"
            ) {
                return children || [];
            }
            const list = Array.isArray(children) ? children : [];
            if (!list.length) {
                return list;
            }
            const section = this.meta?.shop_nav_leaf_counts?.[mainSlug];
            const hasNav =
                section && typeof section === "object" && Object.keys(section).length > 0;
            if (!hasNav) {
                return list;
            }
            return list.filter((ch) => {
                if (this.childRouteMatches(ch, this.currentCatalogSlug)) {
                    return true;
                }
                const slug = this.normalizeCatalogSlugToken(ch.slug || "");
                const id = String(ch.id || "").trim();
                const idKey = id ? `@id:${id}` : "";
                let n;
                if (slug && Object.prototype.hasOwnProperty.call(section, slug)) {
                    n = Number(section[slug]);
                } else if (idKey && Object.prototype.hasOwnProperty.call(section, idKey)) {
                    n = Number(section[idKey]);
                } else {
                    return false;
                }
                return Number.isFinite(n) && n > 0;
            });
        },
        /** يضبط القسم المفتوح فور تغيّر المسار قبل انتظار الجلب حتى لا يومض الأكورديون مغلقاً. */
        preflightSidebarOpenForRoute() {
            const current = this.currentCatalogSlug;
            const mains = this.sidebarMainItems;
            const mainHit = mains.find((m) => this.catalogSlugEquals(m.slug, current));
            if (mainHit) {
                this.sidebarOpenMainSlug = mainHit.slug;
                return;
            }
            const parent = mains.find((m) =>
                (m.children || []).some((ch) => this.childRouteMatches(ch, current))
            );
            if (parent) {
                this.sidebarOpenMainSlug = parent.slug;
                return;
            }
            const h = this.inferMainSectionFromShopSlug(current);
            if (h && mains.some((m) => m.slug === h)) {
                this.sidebarOpenMainSlug = h;
            }
        },
        toggleMainAccordion(mainSlug) {
            this.sidebarOpenMainSlug = mainSlug;
        },
        onMainCategoryClick(mainSlug) {
            this.toggleMainAccordion(mainSlug);
            if (this.catalogSlugEquals(this.currentCatalogSlug, mainSlug)) {
                this.resetFiltersForCategoryChange();
                this.products = [];
                this.fetchProducts(1);
            }
        },
        onChildCategoryClick(mainSlug, child) {
            this.sidebarOpenMainSlug = mainSlug;
            if (this.childRouteMatches(child, this.currentCatalogSlug)) {
                this.resetFiltersForCategoryChange();
                this.products = [];
                this.$nextTick(() => this.fetchProducts(1));
            }
        },
        isTeamsportSubActive(parentSlug) {
            return this.selectedTeamsportSub === parentSlug
                || this.selectedTeamsportSub.startsWith(parentSlug + "/");
        },
        selectTeamsportSub(ch) {
            if (this.selectedTeamsportSub === ch.id) {
                this.selectedTeamsportSub = "";
            } else {
                this.selectedTeamsportSub = ch.id;
            }
            this.products = [];
            this.fetchProducts(1);
        },
        selectTeamsportGrandchild(parentCh, gc) {
            const fullSlug = parentCh.id + "/" + gc.slug;
            if (this.selectedTeamsportSub === fullSlug) {
                this.selectedTeamsportSub = parentCh.id;
            } else {
                this.selectedTeamsportSub = fullSlug;
            }
            this.products = [];
            this.fetchProducts(1);
        },
        resetFiltersForCategoryChange() {
            this.selectedSizes = [];
            this.selectedColors = [];
            this.selectedAttrs = [];
            this.selectedTeamsportSub = "";
            this.q = "";
            this.openFilterKey = "";
            this.syncBrandsFromRoute();
        },
        brandInitials(name) {
            return String(name || "?")
                .split(/\s+/)
                .map((w) => w[0])
                .join("")
                .slice(0, 3)
                .toUpperCase();
        },
        colorToken(c) {
            const name = (c.name || "").trim().toLowerCase();
            const hex = (c.hex || "").trim().toLowerCase();
            return name || hex || "";
        },
        normalizeSizeFacet(entry) {
            if (entry != null && typeof entry === "object" && (entry.key != null || entry.value != null)) {
                const v = String(entry.key ?? entry.value ?? "");
                const label = String(entry.label ?? v);
                const rc = entry.count;
                return {
                    value: v,
                    label,
                    count: rc !== undefined && rc !== null && !Number.isNaN(Number(rc)) ? Number(rc) : null,
                };
            }
            const s = String(entry ?? "");
            return { value: s, label: s, count: null };
        },
        catalogHttpError(e, fallback) {
            const d = e?.response?.data;
            if (d && typeof d === "object" && d.message) return String(d.message);
            if (typeof d === "string" && d.trim()) return d.trim();
            return e?.message || fallback;
        },
        async loadCategories() {
            try {
                this.categoryTree = await fetchCatalogCategories();
                this.syncSidebarOpenState();
            } catch (e) {
                this.categoryTree = [];
                this.errorMsg = this.catalogHttpError(e, "Catalog categories unavailable");
            }
        },
        scrollBrands(dir) {
            this.$nextTick(() => {
                const el = this.$refs.brandStripRef;
                if (!el) return;
                const step = (this.pageLang === "ar" ? -dir : dir) * 220;
                el.scrollBy({ left: step, behavior: "smooth" });
                window.setTimeout(() => this.updateBrandStripOverflow(), 280);
            });
        },
        syncBrandStripScrollOrigin() {
            const el = this.$refs.brandStripRef;
            if (!el) return;
            const maxScroll = Math.max(0, el.scrollWidth - el.clientWidth);
            if (maxScroll <= 1) {
                el.scrollLeft = 0;
                return;
            }
            if (this.pageLang === "ar") {
                if (el.scrollLeft <= 1) {
                    el.scrollLeft = 0;
                }
            } else if (el.scrollLeft < 0) {
                el.scrollLeft = 0;
            }
        },
        updateBrandStripOverflow() {
            const el = this.$refs.brandStripRef;
            if (!el) {
                this.brandStripOverflow = false;
                this.brandStripCanScrollPrev = false;
                return;
            }
            const maxScroll = Math.max(0, el.scrollWidth - el.clientWidth);
            const scrollPos = Math.abs(el.scrollLeft);
            this.brandStripOverflow = maxScroll > 1;
            if (this.pageLang === "ar") {
                this.brandStripCanScrollPrev = scrollPos < maxScroll - 1;
            } else {
                this.brandStripCanScrollPrev = scrollPos > 1;
            }
        },
        isMainActive(mainSlug) {
            if (this.catalogSlugEquals(this.currentCatalogSlug, mainSlug)) {
                return true;
            }
            const main = this.sidebarMainItems.find((m) => m.slug === mainSlug);
            if (!main?.children?.length) {
                return false;
            }
            return main.children.some((ch) => {
                if (this.childRouteMatches(ch, this.currentCatalogSlug)) {
                    return true;
                }
                return (ch.children || []).some((gc) => this.childRouteMatches(gc, this.currentCatalogSlug));
            });
        },
        isChildActive(ch) {
            return this.childRouteMatches(ch, this.currentCatalogSlug);
        },
        /** يفتح أحفاد معدات الحارس عندما الفرع أو أحد أبنائه نشط */
        isEquipmentBranchExpanded(mainSlug, ch) {
            if (mainSlug !== "goalkeeper-equipment" || !ch?.children?.length) {
                return false;
            }
            if (this.isChildActive(ch)) {
                return true;
            }
            return (ch.children || []).some((gc) => this.isChildActive(gc));
        },
        selectBrand(id) {
            this.applyBrandFilterToggle(id);
        },
        clearAllFilters() {
            this.selectedBrands = [];
            this.selectedSizes = [];
            this.selectedColors = [];
            this.selectedAttrs = [];
            this.openFilterKey = "";
            this.showExtraFilters = false;
            const needsBrandUrlClear =
                this.isGoalkeeperGloveBrandLeafRoute()
                || this.$route.query?.brands != null
                || this.$route.query?.brand != null;
            if (needsBrandUrlClear) {
                this.navigateWithSelectedBrands([]);
            }
            this.applyFilters();
        },
        setSort(sortKey) {
            if (this.sort === sortKey) {
                this.openFilterKey = "";
                return;
            }
            this.sort = sortKey;
            this.openFilterKey = "";
            this.applyFilters();
        },
        setSortPreserveDetail(sortKey) {
            if (this.sort === sortKey) return;
            this.sort = sortKey;
            this.applyFilters();
        },
        discountPercent(product) {
            const oldPrice = Number(product?.price?.old || 0);
            const nowPrice = Number(product?.price?.current || 0);
            if (!oldPrice || !nowPrice || oldPrice <= nowPrice) return 0;
            return Math.round(((oldPrice - nowPrice) / oldPrice) * 100);
        },
        cardImageSrc(product) {
            const images = this.productCardImages(product);
            const key = this.productCardKey(product);
            let raw = "";
            if (images.length) {
                const idx = Number(this.cardImageIndex?.[key] ?? 0);
                raw = images[((idx % images.length) + images.length) % images.length] || images[0];
            } else {
                raw = product?.image || "";
            }
            return normalizeCatalogImageUrl(raw);
        },
        slideCardImage(product, dir) {
            const images = this.productCardImages(product);
            if (images.length <= 1) {
                return;
            }
            const key = this.productCardKey(product);
            const current = Number(this.cardImageIndex?.[key] ?? 0);
            const next = (current + dir + images.length) % images.length;
            this.cardImageIndex = {
                ...this.cardImageIndex,
                [key]: next,
            };
        },
        productCardImages(product) {
            return Array.isArray(product?.images) ? product.images.filter(Boolean) : [];
        },
        dedupeProductsBySku(items) {
            const seen = new Set();
            const out = [];
            for (const p of items || []) {
                const key = String(p?.sku || p?.slug || "").trim();
                if (!key || seen.has(key)) {
                    continue;
                }
                seen.add(key);
                out.push(p);
            }
            return out;
        },
        productCardKey(product) {
            return String(product?.sku || product?.slug || "");
        },
        goToProduct(product) {
            const slug = product?.slug;
            if (!slug) {
                return;
            }
            this.$router.push(`/${this.locale}/product/${slug}`);
        },
        onPlpCardClick(product, e) {
            if (e.target?.closest?.(".ks-plp-img-nav-btn")) {
                return;
            }
            if (this._cardSwipeDragged) {
                e.preventDefault();
                e.stopPropagation();
                this._cardSwipeDragged = false;
                return;
            }
            this.goToProduct(product);
        },
        onCardMouseEnter(product) {
            if (typeof window !== "undefined" && window.matchMedia?.("(hover: hover)")?.matches) {
                this.hoveredSku = product?.sku;
            }
            this.prefetchProduct(product);
        },
        onCardMouseLeave() {
            if (typeof window !== "undefined" && window.matchMedia?.("(hover: hover)")?.matches) {
                this.hoveredSku = null;
            }
        },
        prefetchProduct(product) {
            const slug = String(product?.slug || "").trim();
            if (slug) {
                prefetchCatalogProductBySlug(slug);
            }
        },
        onCardImgTouchStart(product, e) {
            if (this.productCardImages(product).length <= 1) {
                return;
            }
            if ((e.touches?.length || 0) > 1) {
                this._cardSwipe = null;
                return;
            }
            const t = e.touches?.[0];
            if (!t) {
                return;
            }
            this.cardImgActiveSku = this.productCardKey(product);
            this._cardSwipeDragged = false;
            this._cardSwipe = {
                key: this.productCardKey(product),
                startX: t.clientX,
                startY: t.clientY,
            };
        },
        onCardImgTouchMove(product, e) {
            if ((e.touches?.length || 0) > 1) {
                this._cardSwipe = null;
                this._cardSwipeDragged = false;
                return;
            }
            if (!this._cardSwipe || this._cardSwipe.key !== this.productCardKey(product)) {
                return;
            }
            const t = e.touches?.[0];
            if (!t) {
                return;
            }
            const dx = t.clientX - this._cardSwipe.startX;
            const dy = t.clientY - this._cardSwipe.startY;
            if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 10) {
                this._cardSwipeDragged = true;
            }
        },
        onCardImgTouchEnd(product, e) {
            if (!this._cardSwipe || this._cardSwipe.key !== this.productCardKey(product)) {
                return;
            }
            const t = e.changedTouches?.[0];
            const state = this._cardSwipe;
            this._cardSwipe = null;
            if (!t) {
                return;
            }
            const dx = readHorizontalSwipeDelta(state, t.clientX, t.clientY);
            if (dx !== null) {
                this.slideCardImage(product, dx < 0 ? 1 : -1);
                this._cardSwipeDragged = true;
                window.setTimeout(() => {
                    this._cardSwipeDragged = false;
                }, 80);
            } else {
                this._cardSwipeDragged = false;
            }
            window.setTimeout(() => {
                if (this.cardImgActiveSku === this.productCardKey(product)) {
                    this.cardImgActiveSku = null;
                }
            }, 120);
        },
        detachCardImgDocSwipe() {
            if (typeof this._cardSwipeDocCleanup === "function") {
                this._cardSwipeDocCleanup();
                this._cardSwipeDocCleanup = null;
            }
        },
        onCardImgMouseDown(product, e) {
            if (
                this.productCardImages(product).length <= 1 ||
                e.button !== 0 ||
                e.pointerType !== "mouse"
            ) {
                return;
            }
            this.detachCardImgDocSwipe();
            this.cardImgActiveSku = this.productCardKey(product);
            this._cardSwipeDragged = false;
            const key = this.productCardKey(product);
            this._cardSwipe = {
                key,
                product,
                startX: e.clientX,
                startY: e.clientY,
            };
            const onMove = (ev) => {
                if (!this._cardSwipe || this._cardSwipe.key !== key) {
                    return;
                }
                const dx = ev.clientX - this._cardSwipe.startX;
                const dy = ev.clientY - this._cardSwipe.startY;
                if (Math.abs(dx) > 8 && Math.abs(dx) > Math.abs(dy)) {
                    this._cardSwipeDragged = true;
                }
            };
            const onUp = (ev) => {
                if (!this._cardSwipe || this._cardSwipe.key !== key) {
                    this.detachCardImgDocSwipe();
                    return;
                }
                const state = this._cardSwipe;
                this._cardSwipe = null;
                this.detachCardImgDocSwipe();
                const dx = readHorizontalSwipeDelta(state, ev.clientX, ev.clientY);
                if (dx !== null) {
                    this.slideCardImage(product, dx < 0 ? 1 : -1);
                    this._cardSwipeDragged = true;
                }
                window.setTimeout(() => {
                    if (this.cardImgActiveSku === key) {
                        this.cardImgActiveSku = null;
                    }
                }, 200);
            };
            document.addEventListener("mousemove", onMove);
            document.addEventListener("mouseup", onUp);
            this._cardSwipeDocCleanup = () => {
                document.removeEventListener("mousemove", onMove);
                document.removeEventListener("mouseup", onUp);
            };
        },
        hasDiscount(product) {
            const oldPrice = Number(product?.price?.old || 0);
            const nowPrice = Number(product?.price?.current || 0);
            if (!oldPrice || !nowPrice || oldPrice <= nowPrice) {
                return false;
            }
            // مثل keepersport: تجاهل فروق التسعير الصغيرة (ليس خصماً حقيقياً)
            const abs = oldPrice - nowPrice;
            const ratio = abs / oldPrice;
            return abs >= 1 || ratio >= 0.02;
        },
        pairForAttr(attrKey, valueKey) {
            return `${attrKey}|${valueKey}`;
        },
        toggleDropdownMenu(key) {
            this.openFilterKey = this.openFilterKey === key ? "" : key;
        },
        handleOutsideClick(event) {
            if (!this.openFilterKey) return;
            const root = this.$el;
            if (!root || !event?.target) return;
            if (!root.contains(event.target)) {
                this.openFilterKey = "";
            }
        },
        syncMobileBodyScroll() {
            if (typeof document === "undefined") return;
            const anyOverlay = this.plpMobileOverlayOpen;
            document.body.style.overflow = anyOverlay ? "hidden" : "";
            document.documentElement.classList.toggle("ks-plp-mob-overlay-open", anyOverlay);
            if (anyOverlay) {
                this.openFilterKey = "";
            }
        },
        armMobileOverlayGuard() {
            this.mobileOverlayGuardUntil = Date.now() + 450;
        },
        mobileOverlayGuardActive() {
            return Date.now() < (this.mobileOverlayGuardUntil || 0);
        },
        queueMobileOverlayOpen(openFn) {
            this.armMobileOverlayGuard();
            window.requestAnimationFrame(() => {
                window.requestAnimationFrame(() => {
                    openFn();
                });
            });
        },
        onMobileBackdropSelfClick(kind) {
            if (this.mobileOverlayGuardActive()) {
                return;
            }
            if (kind === "subcategory") {
                this.closeMobileSubcategorySheet();
                return;
            }
            if (kind === "hub") {
                this.closeMobileFilterHub();
                return;
            }
            this.backdropCloseMobileDetail();
        },
        handleMobileSheetEscape(event) {
            if (event.key !== "Escape") return;
            if (this.mobileFilterDetailKey) {
                this.doneMobileFilterDetail();
                return;
            }
            if (this.mobileFilterHubOpen) {
                this.closeMobileFilterHub();
                return;
            }
            if (this.mobileSubcategoryOpen) {
                this.closeMobileSubcategorySheet();
            }
        },
        countBadge(raw) {
            if (raw == null || raw === "") return null;
            const n = Number(raw);
            return Number.isFinite(n) ? n : null;
        },
        facetRowByKey(key) {
            return this.filterDropdowns.find((d) => d.key === key) || null;
        },
        hubSubtitleForDropdown(dd) {
            const dash = this.lt.facet_empty || "—";
            if (!dd) return dash;
            if (dd.kind === "color") {
                if (!this.selectedColors.length) return dash;
                const labels = this.selectedColors
                    .map((val) => dd.options?.find((x) => x.value === val)?.label)
                    .filter(Boolean);
                return labels.length ? labels.join(", ") : dash;
            }
            if (dd.kind === "size") {
                return this.selectedSizes.length ? this.selectedSizes.join(", ") : dash;
            }
            if (dd.kind === "attr") {
                const prefix = `${dd.attrKey}|`;
                const picks = [];
                for (const pr of this.selectedAttrs) {
                    if (!String(pr).startsWith(prefix)) continue;
                    const vk = String(pr).slice(prefix.length);
                    const o = dd.options?.find(
                        (x) => String(x.value).toLowerCase() === String(vk).toLowerCase(),
                    );
                    picks.push(o?.label || vk);
                }
                return picks.length ? picks.join(", ") : dash;
            }
            return dash;
        },
        openMobileSubcategorySheet() {
            this.mobileFilterHubOpen = false;
            this.mobileFilterDetailKey = "";
            this.mobileDetailOpenedFromHub = false;
            this.queueMobileOverlayOpen(() => {
                this.mobileSubcategoryOpen = true;
            });
        },
        closeMobileSubcategorySheet() {
            this.mobileSubcategoryOpen = false;
        },
        openMobileFilterHub() {
            this.mobileSubcategoryOpen = false;
            this.mobileFilterDetailKey = "";
            this.mobileDetailOpenedFromHub = false;
            this.queueMobileOverlayOpen(() => {
                this.mobileFilterHubOpen = true;
            });
        },
        closeMobileFilterHub() {
            this.mobileFilterHubOpen = false;
        },
        openMobileFilterDetail(key, fromHub = false) {
            if (key === "price") return;
            this.mobileDetailOpenedFromHub = fromHub === true;
            const applyOpen = () => {
                if (!fromHub) {
                    this.mobileFilterHubOpen = false;
                    this.mobileSubcategoryOpen = false;
                } else {
                    this.mobileFilterHubOpen = true;
                }
                this.mobileFilterDetailKey = key;
            };
            if (fromHub) {
                applyOpen();
                return;
            }
            this.queueMobileOverlayOpen(applyOpen);
        },
        closeMobileDetailToHub() {
            this.mobileFilterDetailKey = "";
        },
        doneMobileFilterDetail() {
            this.mobileFilterDetailKey = "";
        },
        backdropCloseMobileDetail() {
            this.doneMobileFilterDetail();
        },
        closeMobileFilterDetailAll() {
            this.mobileFilterDetailKey = "";
            this.mobileFilterHubOpen = false;
            this.mobileDetailOpenedFromHub = false;
        },
        applyShowProductsCloseSubcategory() {
            this.closeMobileSubcategorySheet();
            this.fetchProducts(1);
        },
        applyShowProductsCloseHub() {
            this.closeMobileFilterHub();
            this.fetchProducts(1);
        },
        toggleColorMobile(val, event) {
            const on = !!(event?.target && event.target.checked);
            const v = String(val);
            const i = this.selectedColors.indexOf(v);
            if (on && i < 0) {
                this.selectedColors.push(v);
            } else if (!on && i >= 0) {
                this.selectedColors.splice(i, 1);
            }
            this.applyFilters();
        },
        toggleBrandMobile(id, event) {
            const on = !!(event?.target && event.target.checked);
            const sid = String(id);
            if (on) {
                if (!this.selectedBrands.includes(sid)) {
                    this.applyBrandFilterToggle(sid);
                }
                return;
            }
            if (this.selectedBrands.includes(sid)) {
                this.applyBrandFilterToggle(sid);
            }
        },
        closeFilterDropdown() {
            this.openFilterKey = "";
        },
        isDropdownChecked(row, opt) {
            if (this.isBrandAttributeFacet(row)) {
                return this.isBrandFilterValueSelected(row, opt);
            }
            if (row.kind === "size") return this.selectedSizes.includes(opt.value);
            if (row.kind === "color") return this.selectedColors.includes(opt.value);
            if (row.kind === "attr") return this.selectedAttrs.includes(this.pairForAttr(row.attrKey, opt.value));
            return false;
        },
        toggleDropdownOption(row, opt) {
            if (this.isBrandAttributeFacet(row)) {
                this.applyBrandFilterToggle(opt.value);
                return;
            }
            if (row.kind === "size") {
                const i = this.selectedSizes.indexOf(opt.value);
                if (i >= 0) this.selectedSizes.splice(i, 1);
                else this.selectedSizes.push(opt.value);
                this.applyFilters();
                return;
            }
            if (row.kind === "color") {
                const i = this.selectedColors.indexOf(opt.value);
                if (i >= 0) this.selectedColors.splice(i, 1);
                else this.selectedColors.push(opt.value);
                this.applyFilters();
                return;
            }
            const p = this.pairForAttr(row.attrKey, opt.value);
            const j = this.selectedAttrs.indexOf(p);
            if (j >= 0) this.selectedAttrs.splice(j, 1);
            else this.selectedAttrs.push(p);
            this.applyFilters();
        },
        onSearchInput() {
            if (this.searchDebounceTimer) {
                clearTimeout(this.searchDebounceTimer);
            }
            this.searchDebounceTimer = setTimeout(() => {
                this.applyFilters();
            }, 250);
        },
        async fetchProducts(page = 1, opts = {}) {
            const bootstrap = opts.bootstrap === true && page === 1;
            const reqId = ++this.requestSeq;
            this.catalogFetchInFlight++;
            this.loading = true;
            try {
                const params = {
                    category: this.catalogCategoryForApi,
                    page,
                    per_page: this.perPage,
                    sort: this.sort,
                    q: this.q || undefined,
                    brands: this.selectedBrands.length ? this.selectedBrands : undefined,
                    colors: this.selectedColors.length ? this.selectedColors : undefined,
                };
                if (this.selectedTeamsportSub) {
                    params.teamsport_sub = this.selectedTeamsportSub;
                }
                if (this.selectedSizes.length) params.sizes = this.selectedSizes;
                if (this.selectedAttrs.length) params.attrs = this.selectedAttrs;
                const data = bootstrap && this.currentCatalogSlug !== "sale"
                    ? await fetchCatalogShopListing(params)
                    : await fetchCatalogProducts(params);
                if (reqId !== this.requestSeq) return;
                if (bootstrap && Array.isArray(data.categories)) {
                    this.categoryTree = data.categories;
                }
                if (Array.isArray(data.teamsport_subcategories)) {
                    this.teamsportSubcategories = data.teamsport_subcategories;
                }
                this.products = this.dedupeProductsBySku(data.items);
                this.facets = data.facets;
                this.pagination = data.pagination;
                this.meta = data.meta || {};
                this.errorMsg = "";
                this.$nextTick(() => this.syncSidebarOpenState());
            } catch (e) {
                if (reqId !== this.requestSeq) return;
                this.errorMsg = this.catalogHttpError(e, "Catalog error");
                if (bootstrap && !this.categoryTree.length) {
                    try {
                        await this.loadCategories();
                    } catch {
                        /* ignore */
                    }
                }
                // Keep last successful data visible so the page never appears blank.
                if (!this.products.length) {
                    this.facets = { brands: [], sizes: [], colors: [], attributes: [] };
                    this.pagination = { page: 1, per_page: this.perPage, total: 0, last_page: 1 };
                    const preservedG = this.meta?.gk_apparel_leaf_counts;
                    const preservedN = this.meta?.shop_nav_leaf_counts;
                    this.meta = {};
                    if (preservedG) {
                        this.meta.gk_apparel_leaf_counts = preservedG;
                    }
                    if (preservedN) {
                        this.meta.shop_nav_leaf_counts = preservedN;
                    }
                }
            } finally {
                this.catalogFetchInFlight = Math.max(0, this.catalogFetchInFlight - 1);
                this.loading = this.catalogFetchInFlight > 0;
                this.$nextTick(() => {
                    this.syncBrandStripScrollOrigin();
                    this.updateBrandStripOverflow();
                });
            }
        },
        applyFilters() {
            this.fetchProducts(1);
        },
        goPage(page) {
            this.fetchProducts(page);
        },
        formatPrice(amount, currency) {
            return formatWebsiteCurrency(amount, currency || "SAR", { maximumFractionDigits: 2 });
        },
        formatPriceHtml(amount, currency) {
            return formatWebsitePriceHtml(amount, currency || "SAR", this.pageLang, {
                maximumFractionDigits: 2,
            });
        },
        /** نفس منطق صفحة المنتج: عملة + رقم كبير + كسور علوية */
        pricePromoHtml(amount, currency) {
            return buildPricePromoHtml(amount, currency || "SAR", this.pageLang);
        },
    },
};
</script>

<style scoped>
.ks-plp {
    background: #f5f5f5;
    color: #1a1a1a;
    min-height: 60vh;
    padding: 0 0 48px;
    max-width: 100%;
    overflow-x: clip;
}

.ks-plp-brands-wrap {
    display: flex;
    align-items: stretch;
    gap: 0;
    background: #fff;
    margin: 0 0 6px;
    padding: 6px 4px 6px 0;
    max-width: 100%;
    min-width: 0;
    overflow: hidden;
}

.ks-plp-brands-track {
    direction: ltr;
    flex: 1;
    min-width: 0;
    display: flex;
    flex-wrap: nowrap;
    gap: 4px;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
    align-items: center;
    padding-inline-end: 2px;
}
.ks-plp:lang(ar) .ks-plp-brands-wrap {
    flex-direction: row-reverse;
}
.ks-plp:lang(ar) .ks-plp-brands-track {
    direction: rtl;
}
.ks-plp:lang(ar) .ks-brand-chip {
    direction: ltr;
    unicode-bidi: isolate;
}
.ks-plp:lang(ar) .ks-plp-filters-scroll {
    direction: rtl;
}
.ks-plp:lang(ar) .ks-plp-topbar--desktop .ks-plp-dd {
    direction: ltr;
}
.ks-plp-brands-track::-webkit-scrollbar {
    display: none;
}

.ks-plp-brands-arrow,
.ks-plp-brands-next {
    flex: 0 0 auto;
    align-self: stretch;
    min-width: 26px;
    margin: 0;
    padding: 0 2px 0 4px;
    border: 0;
    background: transparent;
    color: #2a2a2a;
    font-size: 22px;
    font-weight: 300;
    line-height: 1;
    letter-spacing: 0;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: system-ui, "Segoe UI", sans-serif;
}
.ks-plp-brands-arrow--prev {
    padding: 0 4px 0 2px;
}
.ks-plp-brands-arrow:hover:not(:disabled),
.ks-plp-brands-next:hover:not(:disabled) {
    color: #000;
}
.ks-plp-brands-arrow:disabled,
.ks-plp-brands-arrow--muted,
.ks-plp-brands-next:disabled,
.ks-plp-brands-next--muted {
    opacity: 0.35;
    cursor: default;
}

.ks-brand-chip {
    flex: 0 0 auto;
    min-height: 28px;
    padding: 0 11px;
    border: 1px solid #c8c8c8;
    background: #fff;
    color: #111;
    font-size: 11px;
    font-weight: 700;
    line-height: 26px;
    letter-spacing: 0.04em;
    cursor: pointer;
    border-radius: 2px;
}
.ks-plp:lang(en) .ks-brand-chip {
    text-transform: uppercase;
}
.ks-plp:lang(ar) .ks-brand-chip {
    letter-spacing: 0;
}
.ks-brand-chip--active {
    border-color: #1a1a1a;
    background: #f6f6f6;
}

.ks-plp-inner {
    max-width: 100%;
    margin: 0 auto;
    padding: 1px 8px 0;
    background: #373e3e;
    overflow-x: clip;
    box-sizing: border-box;
}

.ks-plp-bc {
    font-size: 12px;
    color: #7b7b7b;
    margin-bottom: 0;
    display: none;
}
.ks-plp-head {
    background: #fff;
    border: 0;
    padding: 0 0 8px;
}
.ks-plp-title {
    margin: 0;
    font-size: 40px;
    font-weight: 500;
    color: #303030;
    line-height: 1;
}
.ks-plp-title-count {
    font-size: 14px;
    color: #8a8a8a;
    margin-inline-start: 6px;
}
.ks-plp-bc a {
    color: #555;
    text-decoration: none;
}
.ks-plp-bc-sep {
    margin: 0 6px;
    color: #999;
}
.ks-plp-bc-current {
    font-weight: 600;
    color: #111;
}

.ks-plp-hero {
    background: #fff;
    border: 1px solid #e0e0e0;
    padding: 22px 20px 18px;
    margin-bottom: 12px;
}
.ks-plp-sub {
    margin: 8px 0 0;
    font-size: 15px;
    color: #444;
}
.ks-plp-meta {
    margin: 12px 0 0;
    font-size: 13px;
    color: #666;
}
.ks-plp-debug {
    margin: 6px 0 0;
    font-size: 11px;
    color: #999;
}

.ks-plp-filters-scroll {
    max-width: 100%;
    min-width: 0;
    position: relative;
    z-index: 5;
    overflow-x: auto;
    overflow-y: visible;
    scrollbar-width: none;
    -ms-overflow-style: none;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior-x: contain;
    background: #fff;
}
.ks-plp-filters-scroll::-webkit-scrollbar {
    display: none;
}
@media (min-width: 1001px) {
    .ks-plp-filters-scroll {
        overflow-x: visible;
    }
}

.ks-plp-topbar {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff;
    border: 0;
    padding: 2px 0 12px;
    margin-bottom: 0;
    flex-wrap: nowrap;
    min-width: min-content;
}
@media (min-width: 1001px) {
    .ks-plp-topbar--desktop {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        min-width: 0;
    }
    .ks-plp-topbar--desktop {
        display: inline-flex !important;
    }
}
@media (max-width: 1000px) {
    .ks-plp-topbar--desktop {
        display: inline-flex !important;
        flex-wrap: nowrap;
        width: auto;
        min-width: min-content;
    }
}
.ks-plp-topbar--desktop .ks-plp-dd {
    flex: 0 0 auto;
}
.ks-plp-topbar--desktop .ks-plp-dd--sort {
    margin-inline-start: 0;
}
@media (min-width: 1001px) {
    .ks-plp-topbar--desktop .ks-plp-dd--sort {
        margin-inline-start: auto;
    }
}
.ks-plp-filters-scroll.is-dropdown-open {
    overflow: visible !important;
    z-index: 500;
}
.ks-plp--filter-open {
    overflow-x: visible;
}
.ks-plp--filter-open .ks-plp-inner,
.ks-plp--filter-open .ks-plp-main {
    overflow-x: visible;
}
.ks-plp--filter-open .ks-plp-dd.is-open {
    z-index: 510;
}
.ks-plp--filter-open .ks-plp-dd-menu {
    z-index: 520;
}
.ks-plp-dd {
    position: relative;
}
.ks-plp-dd.is-open {
    z-index: 30;
}
.ks-plp-dd-trigger {
    border: 1px solid #c8c8c8;
    background: #fff;
    min-height: 28px;
    padding: 0 10px;
    font-size: 11px;
    font-weight: 700;
    color: #111;
    display: inline-flex;
    gap: 6px;
    align-items: center;
    cursor: pointer;
    border-radius: 2px;
    letter-spacing: 0.04em;
    transition:
        background-color 0.15s ease,
        border-color 0.15s ease;
}
.ks-plp:lang(en) .ks-plp-dd-trigger {
    text-transform: uppercase;
}
.ks-plp:lang(ar) .ks-plp-dd-trigger {
    letter-spacing: 0;
}
.ks-plp-dd-trigger:hover {
    background: #fafafa;
    border-color: #b0b0b0;
}
.ks-plp-dd-trigger--primary {
    border-color: #1a1a1a;
    font-weight: 700;
}
.ks-plp-dd-trigger--primary:hover {
    background: #fafafa;
    border-color: #000;
}
.ks-plp-dd-caret {
    display: inline-block;
    width: 0;
    height: 0;
    border-left: 3.5px solid transparent;
    border-right: 3.5px solid transparent;
    border-top: 5px solid #1a1a1a;
    flex-shrink: 0;
    opacity: 1;
}
.ks-plp-dd-caret--more {
    border: 0;
    border-left: 5px solid #1a1a1a;
    border-top: 3.5px solid transparent;
    border-bottom: 3.5px solid transparent;
    width: 0;
    height: 0;
}
.ks-plp:lang(ar) .ks-plp-dd-caret--more {
    border-left: 0;
    border-right: 5px solid #1a1a1a;
    border-top: 3.5px solid transparent;
    border-bottom: 3.5px solid transparent;
}
.ks-plp-dd-count {
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    background: #111;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    line-height: 1;
}
.ks-plp-dd--sort {
    margin-inline-start: auto;
}
.ks-plp-dd-menu {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    min-width: 210px;
    max-height: 260px;
    overflow: auto;
    z-index: 20;
    background: #fff;
    border: 0;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    padding: 8px;
}
.ks-plp-dd-menu--facet {
    padding: 10px;
}
.ks-plp-dd-menu--wide {
    min-width: 290px;
    max-height: min(65vh, 520px);
}
.ks-plp-dd--sort .ks-plp-dd-menu--sort {
    inset-inline-start: auto;
    inset-inline-end: 0;
    min-width: 200px;
    padding: 4px 0;
    border: 1px solid #ddd;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}
.ks-plp-dd-group {
    padding-bottom: 8px;
    margin-bottom: 8px;
    border-bottom: 1px solid #efefef;
}
.ks-plp-dd-group:last-child {
    border-bottom: 0;
    margin-bottom: 2px;
    padding-bottom: 0;
}
.ks-plp-dd-group-title {
    margin: 0 0 6px;
    font-size: 12px;
    font-weight: 700;
    color: #333;
}
.ks-plp-dd-option {
    display: flex;
    gap: 8px;
    align-items: center;
    padding: 5px 4px;
    font-size: 12px;
    cursor: pointer;
}
.ks-plp-dd-option input {
    margin: 0;
}
.ks-plp-dd-opt-count {
    margin-inline-start: auto;
    font-size: 11px;
    font-weight: 600;
    color: #555;
    background: #eaeaea;
    border-radius: 4px;
    padding: 3px 8px;
}
.ks-plp-dd-done {
    width: 100%;
    border: 0;
    border-top: 1px solid #ececec;
    background: #fff;
    text-align: left;
    padding: 8px 4px 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 4px;
}
.ks-plp:lang(ar) .ks-plp-dd-done {
    text-align: right;
}

/* Mobile PLP toolbar + KEEPERsport-style sheets (Teleport to body) */
.ks-plp-topbar--mobile {
    display: none;
    flex-wrap: nowrap;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    scrollbar-width: none;
    padding-bottom: 6px;
    -webkit-overflow-scrolling: touch;
}
.ks-plp-topbar--mobile::-webkit-scrollbar {
    display: none;
}
.ks-mob-inline-chip {
    flex: 0 0 auto;
}
/* Sticky mobile filter strip (under site header) */
.ks-plp-mobile-sticky-bar {
    display: none;
}

.ks-plp-mobile-plp-tools {
    display: none;
    gap: 10px;
    margin: 10px 0 0;
    align-items: stretch;
}
.ks-plp-mobile-split-btn {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 34px;
    padding: 0 14px;
    border: none;
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    border-radius: 20px;
    letter-spacing: 0.04em;
    white-space: nowrap;
    transition: background 0.15s;
}
.ks-plp-mobile-split-btn:active {
    background: rgba(255, 255, 255, 0.28);
}
.ks-plp:lang(en) .ks-plp-mobile-split-btn {
    text-transform: uppercase;
}
.ks-plp-mobile-split-label {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: min(52vw, 220px);
}
.ks-plp-mobile-split-caret {
    flex-shrink: 0;
    width: 0;
    height: 0;
    border-left: 3px solid transparent;
    border-right: 3px solid transparent;
    border-top: 4px solid #fff;
}

.ks-mob-backdrop {
    position: fixed;
    inset: 0;
    z-index: 11000;
    background: rgba(0, 0, 0, 0.55);
    overscroll-behavior: contain;
    display: flex;
    justify-content: flex-start;
    align-items: stretch;
    touch-action: none;
}
.ks-mob-backdrop--hub {
    justify-content: center;
    align-items: flex-end;
    padding: 0;
}
.ks-mob-backdrop--detail {
    justify-content: center;
    align-items: flex-end;
    padding: 0;
}
.ks-mob-drawer {
    width: min(90vw, 440px);
    background: #fff;
    color: #111;
    box-shadow: 6px 0 28px rgba(0, 0, 0, 0.18);
    display: flex;
    flex-direction: column;
    outline: none;
}
.ks-mob-drawer-scroll {
    flex: 1;
    overflow-y: auto;
    padding: calc(22px + env(safe-area-inset-top)) 22px 12px;
    -webkit-overflow-scrolling: touch;
}
.ks-mob-drawer-scroll--sub {
    padding-top: 8px;
}
.ks-mob-drawer-heading {
    margin: 0;
    padding: calc(14px + env(safe-area-inset-top)) 22px 0;
    font-size: 17px;
    font-weight: 800;
}
.ks-mob-cat-block {
    margin-bottom: 20px;
}
.ks-mob-cat-head {
    display: block;
    font-size: 18px;
    font-weight: 800;
    color: #111;
    text-decoration: none;
    margin-bottom: 8px;
}
.ks-mob-cat-sublist {
    list-style: none;
    margin: 0;
    padding: 0 0 0 4px;
}
.ks-plp:lang(ar) .ks-mob-cat-sublist {
    padding: 0 4px 0 0;
}
.ks-mob-cat-sublist li {
    margin: 0 0 8px;
}
.ks-mob-cat-sublist a {
    color: #333;
    font-size: 15px;
    text-decoration: none;
    font-weight: 600;
}
.ks-mob-cat-sublist a:hover {
    text-decoration: underline;
}
.ks-mob-cat-sublist--solo {
    padding-top: 4px;
}
.ks-mob-cat-sale-inline {
    display: inline-block;
    margin-top: 16px;
    font-size: 16px;
    font-weight: 800;
    color: #cf1f1f;
    text-decoration: none;
}

.ks-mob-cta {
    border: 0;
    border-radius: 6px;
    background: #ffcc00;
    color: #111;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 0.04em;
    cursor: pointer;
    flex-shrink: 0;
}
.ks-mob-cta--footer {
    margin: 8px 16px calc(12px + env(safe-area-inset-bottom));
    padding: 14px 16px;
    width: auto;
}

.ks-mob-hub-sheet {
    width: 100%;
    max-width: 520px;
    max-height: min(92dvh, 100%);
    min-height: min(72dvh, 100%);
    background: #fff;
    border-radius: 16px 16px 0 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 -14px 40px rgba(0, 0, 0, 0.2);
}
.ks-mob-hub-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 8px 8px 0;
    min-height: 0;
}
.ks-mob-hub-row {
    display: flex;
    width: 100%;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 16px 8px;
    border: 0;
    border-bottom: 1px solid #e6e6e6;
    background: #fff;
    color: inherit;
    text-align: inherit;
    cursor: pointer;
}
.ks-mob-hub-row:last-of-type {
    border-bottom-color: transparent;
}
.ks-mob-hub-row:disabled {
    opacity: 0.42;
    cursor: default;
}
.ks-mob-hub-row-title {
    font-size: 15px;
    font-weight: 800;
}
.ks-plp:lang(en) .ks-mob-hub-row-title {
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.04em;
}
.ks-mob-hub-row-sub {
    margin-top: 4px;
    font-size: 13px;
    color: #6e6e6e;
}
.ks-mob-hub-chevron {
    font-size: 20px;
    color: #999;
}

.ks-mob-detail-sheet {
    width: 100%;
    max-width: 520px;
    max-height: min(92dvh, 100%);
    min-height: min(72dvh, 100%);
    background: #fff;
    border-radius: 16px 16px 0 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 -14px 40px rgba(0, 0, 0, 0.2);
}
.ks-mob-detail-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 12px 8px calc(14px + env(safe-area-inset-left));
    border-bottom: 1px solid #ededed;
}
.ks-plp:lang(ar) .ks-mob-detail-head {
    padding-inline: calc(14px + env(safe-area-inset-right)) 12px;
}
.ks-mob-detail-back {
    border: 0;
    background: transparent;
    font-size: 28px;
    line-height: 1;
    padding: 0 4px;
    cursor: pointer;
    color: #111;
}
.ks-plp:lang(ar) .ks-mob-detail-back {
    transform: scaleX(-1);
}
.ks-mob-detail-title {
    flex: 1;
    margin: 0;
    font-size: 13px;
    font-weight: 800;
}
.ks-plp:lang(en) .ks-mob-detail-title {
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.ks-mob-detail-body {
    flex: 1;
    overflow-y: auto;
    padding: 10px 16px 72px;
    min-height: 0;
}
.ks-mob-radio-row {
    display: block;
    width: 100%;
    text-align: start;
    border: 0;
    background: #fff;
    padding: 13px 4px;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
}
.ks-mob-radio-row.is-on {
    color: #000;
    font-weight: 800;
}
.ks-mob-check-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 4px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
    cursor: pointer;
    margin: 0;
}
.ks-mob-check-label {
    flex: 1;
}
.ks-mob-count {
    font-size: 12px;
    font-weight: 700;
    color: #444;
    background: #e8e8e8;
    border-radius: 5px;
    padding: 3px 8px;
    flex-shrink: 0;
}
.ks-mob-color-radio {
    accent-color: #111;
}
.ks-mob-done-fab {
    align-self: flex-end;
    margin: -56px 16px calc(14px + env(safe-area-inset-bottom));
    border: 0;
    border-radius: 6px;
    background: #ffcc00;
    color: #111;
    font-size: 14px;
    font-weight: 800;
    padding: 12px 24px;
    cursor: pointer;
    z-index: 2;
}
.ks-mob-muted {
    color: #777;
    font-size: 14px;
    margin: 12px 0;
}
.ks-mob-subcat-link {
    display: block;
    padding: 12px 4px;
    font-weight: 600;
    font-size: 15px;
    color: #111;
    text-decoration: none;
    border-bottom: 1px solid #eee;
}
.ks-mob-cta--check {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.ks-plp-sort-opt {
    width: 100%;
    border: 0;
    background: #fff;
    text-align: start;
    padding: 9px 14px;
    font-size: 12px;
    color: #444;
    cursor: pointer;
}
.ks-plp-sort-opt:hover {
    background: #f5f5f5;
}
.ks-plp-sort-opt.is-active {
    background: #f5f0e8;
    color: #222;
}

.ks-plp-side {
    background: #1f2427;
    border: 1px solid #2d3439;
    padding: 14px 12px;
    position: sticky;
    top: 0;
}
.ks-plp-side-title {
    margin: 0 0 12px;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    border-bottom: 0;
    padding-bottom: 0;
    color: #fff;
}
.ks-plp-cat-tree {
    list-style: none;
    margin: 0 0 12px;
    padding: 0;
    font-size: 13px;
}
.ks-plp-cat-root {
    margin-bottom: 10px;
}
.ks-plp-cat-main-row {
    display: flex;
    align-items: center;
}
.ks-plp-cat-link {
    font-weight: 700;
    color: #f0f0f0;
    text-decoration: none;
    display: block;
    padding: 6px 8px;
    width: 100%;
    border-left: 2px solid transparent;
}
.ks-plp-cat-link:hover {
    color: #fff;
}
.ks-plp-cat-link.is-active {
    border-left-color: #efc527;
    background: #2a3136;
    color: #fff;
}
.ks-plp-cat-children {
    list-style: none;
    margin: 6px 0 0;
    padding: 0 0 0 10px;
    border-left: 1px solid #384047;
}
.ks-plp-cat-sublink {
    display: block;
    padding: 5px 6px;
    color: #d2d2d2;
    text-decoration: none;
    font-size: 12px;
}
.ks-plp-cat-sublink:hover {
    color: #fff;
}
.ks-plp-cat-sublink.is-active {
    color: #efc527;
    font-weight: 700;
}
.ks-plp-cat-grandchildren {
    list-style: none;
    margin: 4px 0 0;
    padding: 0 0 0 12px;
    border-left: 1px solid #384047;
}
.ks-plp-cat-sublink--l3 {
    font-size: 11px;
    padding: 4px 6px;
}
.ks-mob-cat-l3 {
    list-style: none;
    margin: 0;
    padding: 0 0 0 16px;
}
.ks-mob-cat-l3 a {
    display: block;
    padding: 6px 0;
    font-size: 13px;
    color: #aaa;
    text-decoration: none;
}
.ks-mob-cat-l3 a:hover {
    color: #fff;
}
.ks-mob-cat-sub-parent.is-active {
    color: #efc527;
    font-weight: 700;
}

.ks-plp-btn {
    min-height: 36px;
    border-radius: 2px;
    font-weight: 700;
    font-size: 12px;
    cursor: pointer;
    border: 1px solid #ccc;
    background: #fff;
}
.ks-plp-btn--primary {
    background: #efc527;
    border-color: #d4ae22;
    color: #111;
}
.ks-plp-btn--ghost {
    background: #fff;
}
.ks-plp-btn--block {
    width: 100%;
    margin-top: 8px;
}
.ks-plp-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.ks-plp-main {
    min-width: 0;
    max-width: 100%;
    overflow-x: clip;
    background: #fff;
    padding: 27px 19px 16px;
    container-type: inline-size;
    container-name: plp-main;
}

/* شبكة المنتجات — keepersport.net (عرض الشاشة + عرض منطقة المنتجات) */
.ks-plp-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 6px;
    position: relative;
    z-index: 0;
}

/* بدون قائمة جانبية */
@media (min-width: 556px) {
    .ks-plp:not(.ks-plp--has-sidebar) .ks-plp-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (min-width: 991px) {
    .ks-plp:not(.ks-plp--has-sidebar) .ks-plp-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

/* مع القائمة الجانبية */
@media (min-width: 1001px) {
    .ks-plp.ks-plp--has-sidebar .ks-plp-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (min-width: 1301px) {
    .ks-plp.ks-plp--has-sidebar .ks-plp-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

/* ضبط دقيق عند ظهور القائمة الجانبية (حسب عرض عمود المنتجات الفعلي) */
@container plp-main (min-width: 720px) {
    .ks-plp.ks-plp--has-sidebar .ks-plp-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@container plp-main (min-width: 980px) {
    .ks-plp.ks-plp--has-sidebar .ks-plp-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}
.ks-plp-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    position: relative;
    z-index: 0;
}
.ks-plp-skeleton {
    position: relative;
    z-index: 0;
}
.ks-plp-product-list--refreshing {
    opacity: 0.55;
    pointer-events: none;
    transition: opacity 0.15s ease;
}

/* شيمر تحميل المنتجات (مشابه لمواقع التجارة) */
@keyframes ks-plp-shimmer {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}
.ks-plp-skel-shimmer {
    background: linear-gradient(
        110deg,
        #e4e4e4 0%,
        #f5f5f5 45%,
        #ebebeb 55%,
        #e4e4e4 100%
    );
    background-size: 200% 100%;
    animation: ks-plp-shimmer 1.35s ease-in-out infinite;
}
@media (prefers-reduced-motion: reduce) {
    .ks-plp-skel-shimmer {
        animation: none;
        background: #e8e8e8;
    }
}
.ks-plp-skeleton {
    min-height: 240px;
}
.ks-plp-skeleton-card {
    display: flex;
    flex-direction: column;
    min-height: 348px;
    background: #f5f5f5;
    overflow: hidden;
}
.ks-plp-skeleton-card--list {
    flex-direction: row;
    align-items: stretch;
    min-height: 180px;
}
.ks-plp-skeleton-img {
    width: 100%;
    aspect-ratio: 1 / 0.9;
    flex-shrink: 0;
}
.ks-plp-skeleton-card--list .ks-plp-skeleton-img {
    width: 160px;
    max-width: 160px;
    aspect-ratio: auto;
    min-height: 140px;
}
.ks-plp-skeleton-body {
    flex: 1;
    padding: 14px 12px 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 0;
}
.ks-plp-skeleton-line {
    height: 12px;
    border-radius: 2px;
    max-width: 100%;
}
.ks-plp-skeleton-line--brand {
    width: 38%;
    height: 10px;
}
.ks-plp-skeleton-line--title {
    width: 92%;
}
.ks-plp-skeleton-line--price {
    width: 44%;
    height: 14px;
    margin-top: 4px;
}
.ks-plp-skeleton-card--list .ks-plp-skeleton-body {
    justify-content: center;
}
.ks-plp-card {
    position: relative;
    overflow: hidden;
    border: 0;
    display: flex;
    flex-direction: column;
    min-height: 348px;
    background: #f5f5f5;
    transition: background-color 0.22s ease;
    cursor: pointer;
    touch-action: auto;
    -webkit-tap-highlight-color: transparent;
}
.ks-plp-card:hover {
    background: #ebebeb;
}
.ks-plp-card--list {
    flex-direction: row;
    align-items: stretch;
}
.ks-plp-card-img-wrap {
    display: block;
    line-height: 0;
    background: transparent;
    position: relative;
    overflow: hidden;
    z-index: 0;
    touch-action: auto;
    -webkit-tap-highlight-color: transparent;
    user-select: none;
    -webkit-user-select: none;
}
.ks-plp-card-img {
    width: 100%;
    aspect-ratio: 1 / 0.9;
    object-fit: contain;
    display: block;
    pointer-events: none;
    -webkit-user-drag: none;
    user-select: none;
}
.ks-plp-img-swipe-layer {
    position: absolute;
    inset: 0;
    z-index: 1;
    touch-action: auto;
    cursor: grab;
    background: transparent;
}
.ks-plp-img-swipe-layer:active {
    cursor: grabbing;
}
.ks-plp-img-nav {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    pointer-events: none;
    padding: 0 6px;
    z-index: 3;
}
.ks-plp-img-nav-btn {
    pointer-events: auto;
    width: 22px;
    height: 22px;
    border: 1px solid #cfcfcf;
    background: rgba(255, 255, 255, 0.94);
    color: #333;
    font-size: 15px;
    line-height: 1;
    cursor: pointer;
}
.ks-plp-card--list .ks-plp-card-img-wrap {
    flex: 0 0 160px;
    max-width: 160px;
}
.ks-plp-card-sale-flag {
    position: absolute;
    top: 6px;
    left: -15px;
    z-index: 3;
    background: #d3d3d3;
    color: #202020;
    font-size: 11px;
    font-weight: 700;
    width: 66px;
    height: 22px;
    text-align: center;
    padding: 0;
    line-height: 22px;
    letter-spacing: 0.02em;
    transform: rotate(-45deg);
    box-shadow: 0 0 3px rgba(0, 0, 0, 0.22);
}
.ks-plp-card-sale-flag.is-nosale {
    display: none;
}
.ks-plp-card-corner {
    display: none;
}
.ks-plp-card-body {
    position: relative;
    z-index: 2;
    padding: 8px 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
    text-align: center;
    align-items: center;
    background: transparent;
    border: 0;
    box-shadow: none;
}
.ks-plp-card-brand {
    margin: 0;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0;
    text-transform: lowercase;
    color: #2f2f2f;
}
.ks-plp-card-name {
    margin: 0;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.35;
    color: #464646;
    min-height: 2.4em;
}
.ks-plp-card:hover .ks-plp-card-name {
    text-decoration: underline;
}
.ks-plp-card-price {
    margin-top: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding-top: 8px;
    width: 100%;
}
.ks-plp-price-old {
    display: inline-block;
    font-size: 13px;
    color: #8a8a8a;
    font-weight: 400;
    line-height: 1.25;
    text-align: center;
}
.ks-plp-card-price-now {
    margin: 0;
    padding: 0;
    text-align: center;
    width: 100%;
    line-height: 1.05;
    color: #111;
    font-size: clamp(1.65rem, 3.2vw, 2.35rem);
    font-weight: 600;
}
.ks-plp-card-price-now.is-sale {
    color: #111;
}
.ks-plp-card-price-now :deep(.ks-sale-cur) {
    font-size: 0.58em;
    font-weight: 500;
    vertical-align: baseline;
    margin-inline-end: 0.05em;
}
.ks-plp-card-price-now :deep(.ks-sale-major) {
    font-size: 1em;
    font-weight: 600;
    letter-spacing: -0.025em;
}
.ks-plp-card-price-now :deep(.ks-sale-minor) {
    font-size: 0.38em;
    font-weight: 400;
    vertical-align: super;
    margin-inline-start: 0.02em;
}
.ks-plp-price-after {
    display: none;
}
.ks-plp-limited-time {
    display: none;
}

.ks-plp-error {
    background: #fff4f4;
    border: 1px solid #e7b3b3;
    color: #8a1c1c;
    padding: 14px 16px;
    margin-bottom: 12px;
}

.ks-plp-state {
    background: #fff;
    border: 1px solid #e0e0e0;
    padding: 24px;
    color: #666;
}
.ks-plp-state--pending {
    color: #444;
    font-weight: 600;
}
.ks-plp-inline-loading {
    margin: 0 0 10px;
    font-size: 12px;
    color: #666;
}

.ks-plp-split {
    display: grid;
    grid-template-columns: 288px 1fr;
    gap: 0;
    align-items: start;
    margin-top: 12px;
    min-width: 0;
    max-width: 100%;
    overflow-x: clip;
}
.ks-plp-pager {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    margin-top: 22px;
    padding: 16px 12px 10px;
    background: #fff;
    border: 0;
    flex-wrap: wrap;
}
.ks-plp-pager--numbered {
    border-top: 1px solid #e6e6e6;
}
.ks-plp-pager-arrow {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 0;
    background: transparent;
    color: #2a2a2a;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: background-color 0.15s ease, color 0.15s ease;
}
.ks-plp-pager-arrow:hover:not(:disabled) {
    background: #f0f0f0;
}
.ks-plp-pager-arrow:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
.ks-plp-pager-pages {
    list-style: none;
    margin: 0;
    padding: 0 4px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.ks-plp-pager-pages li {
    margin: 0;
    padding: 0;
}
.ks-plp-pager-num {
    min-width: 38px;
    height: 38px;
    padding: 0 6px;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #333;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    line-height: 1;
    transition: background-color 0.15s ease, color 0.15s ease;
}
.ks-plp-pager-num:hover:not(:disabled):not(.is-active) {
    background: #f3f3f3;
}
.ks-plp-pager-num.is-active {
    background: #e4e4e4;
    color: #111;
    font-weight: 600;
    cursor: default;
}
.ks-plp-pager-num:disabled {
    cursor: wait;
    opacity: 0.7;
}
.ks-plp-pager-meta {
    font-size: 13px;
    color: #555;
}

@media (max-width: 1000px) {
    .ks-plp {
        padding-bottom: 88px;
    }
    .ks-plp-split {
        grid-template-columns: 1fr;
    }
    .ks-plp-side {
        display: none;
    }
    .ks-plp-brands-desktop {
        display: flex !important;
    }
    .ks-plp-mobile-sticky-bar {
        display: block;
        position: fixed;
        bottom: max(16px, env(safe-area-inset-bottom, 16px));
        left: 50%;
        transform: translateX(-50%);
        z-index: 10900;
        background: rgba(17, 17, 17, 0.92);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 6px 10px;
        border-radius: 28px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        width: auto;
        max-width: calc(100vw - 32px);
    }
    .ks-plp-mobile-plp-tools {
        display: flex !important;
        margin: 0;
        gap: 6px;
    }
    .ks-plp-dd-menu--facet {
        min-width: min(296px, calc(100vw - 24px));
        max-height: min(320px, 52vh);
    }
    .ks-plp.ks-plp--phone .ks-plp-head {
        display: none !important;
    }
}
@media (min-width: 1001px) {
    html:not(.ks-touch-nav):not(.ks-desktop-site-mobile) .ks-plp:not(.ks-plp--phone) .ks-plp-topbar--mobile {
        display: none !important;
    }
    html:not(.ks-touch-nav):not(.ks-desktop-site-mobile) .ks-plp:not(.ks-plp--phone) .ks-plp-mobile-sticky-bar {
        display: none !important;
    }
    html:not(.ks-touch-nav):not(.ks-desktop-site-mobile) .ks-plp:not(.ks-plp--phone) .ks-plp-side {
        display: block !important;
    }
    html:not(.ks-touch-nav):not(.ks-desktop-site-mobile) .ks-plp:not(.ks-plp--phone) .ks-plp-split {
        grid-template-columns: 288px 1fr !important;
    }
    html:not(.ks-touch-nav):not(.ks-desktop-site-mobile) .ks-plp:not(.ks-plp--phone) .ks-plp-head {
        display: block !important;
    }
}

/* هاتف: لا شريط تصنيفات جانبي — حتى لو العرض 1024 («مصمم للكمبيوتر») */
.ks-plp.ks-plp--phone .ks-plp-side {
    display: none !important;
}
.ks-plp.ks-plp--phone .ks-plp-split {
    grid-template-columns: 1fr !important;
}
.ks-plp.ks-plp--phone .ks-plp-head {
    display: none !important;
}
.ks-plp.ks-plp--phone .ks-plp-brands-desktop {
    display: flex !important;
}
.ks-plp.ks-plp--phone .ks-plp-mobile-sticky-bar {
    display: block;
    position: fixed;
    bottom: max(16px, env(safe-area-inset-bottom, 16px));
    left: 50%;
    transform: translateX(-50%);
    z-index: 100;
    background: rgba(17, 17, 17, 0.92);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    padding: 6px 10px;
    border-radius: 28px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    width: auto;
    max-width: calc(100vw - 32px);
}
.ks-plp.ks-plp--phone .ks-plp-topbar--mobile,
html.ks-touch-nav .ks-plp-topbar--mobile,
html.ks-desktop-site-mobile .ks-plp-topbar--mobile {
    display: none !important;
}
.ks-plp.ks-plp--phone .ks-plp-mobile-plp-tools {
    display: flex !important;
}

@media (max-width: 1000px) {
    .ks-plp.ks-plp--phone .ks-plp-head,
    html.ks-touch-nav .ks-plp-head,
    html.ks-desktop-site-mobile .ks-plp-head {
        display: none !important;
    }
    html.ks-touch-nav .ks-plp-side,
    html.ks-desktop-site-mobile .ks-plp-side,
    .ks-plp.ks-plp--phone .ks-plp-side {
        display: none !important;
    }
    html.ks-touch-nav .ks-plp-split,
    html.ks-desktop-site-mobile .ks-plp-split,
    .ks-plp.ks-plp--phone .ks-plp-split {
        grid-template-columns: 1fr;
    }
    html.ks-touch-nav .ks-plp-mobile-sticky-bar,
    html.ks-desktop-site-mobile .ks-plp-mobile-sticky-bar,
    .ks-plp.ks-plp--phone .ks-plp-mobile-sticky-bar {
        display: block;
        position: fixed;
        bottom: max(16px, env(safe-area-inset-bottom, 16px));
        left: 50%;
        transform: translateX(-50%);
        z-index: 10900;
    }
    html.ks-touch-nav .ks-plp-mobile-plp-tools,
    html.ks-desktop-site-mobile .ks-plp-mobile-plp-tools,
    .ks-plp.ks-plp--phone .ks-plp-mobile-plp-tools {
        display: flex !important;
    }
    html.ks-touch-nav .ks-plp-brands-desktop,
    html.ks-desktop-site-mobile .ks-plp-brands-desktop,
    .ks-plp.ks-plp--phone .ks-plp-brands-desktop {
        display: flex !important;
    }
}

@media (min-width: 1001px) {
    html.ks-touch-nav .ks-plp-side,
    html.ks-desktop-site-mobile .ks-plp-side,
    .ks-plp.ks-plp--phone .ks-plp-side {
        display: none !important;
    }
    html.ks-touch-nav .ks-plp-split,
    html.ks-desktop-site-mobile .ks-plp-split,
    .ks-plp.ks-plp--phone .ks-plp-split {
        grid-template-columns: 1fr !important;
    }
    html.ks-touch-nav .ks-plp-head,
    html.ks-desktop-site-mobile .ks-plp-head,
    .ks-plp.ks-plp--phone .ks-plp-head {
        display: none !important;
    }
    html.ks-touch-nav .ks-plp-mobile-sticky-bar,
    html.ks-desktop-site-mobile .ks-plp-mobile-sticky-bar,
    .ks-plp.ks-plp--phone .ks-plp-mobile-sticky-bar {
        display: block;
        position: fixed;
        bottom: max(16px, env(safe-area-inset-bottom, 16px));
        left: 50%;
        transform: translateX(-50%);
        z-index: 10900;
        background: rgba(17, 17, 17, 0.92);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 6px 10px;
        border-radius: 28px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        width: auto;
        max-width: calc(100vw - 32px);
    }
    html.ks-touch-nav .ks-plp-mobile-plp-tools,
    html.ks-desktop-site-mobile .ks-plp-mobile-plp-tools,
    .ks-plp.ks-plp--phone .ks-plp-mobile-plp-tools {
        display: flex !important;
    }
    html.ks-touch-nav .ks-plp-brands-desktop,
    html.ks-desktop-site-mobile .ks-plp-brands-desktop,
    .ks-plp.ks-plp--phone .ks-plp-brands-desktop {
        display: flex !important;
    }
}

</style>

<!-- قواعد PLP للجوال — بدون scoped -->
<style>
html.ks-touch-nav .ks-plp,
html.ks-desktop-site-mobile .ks-plp,
.ks-plp {
    max-width: 100%;
    overflow-x: clip;
}
html.ks-touch-nav .ks-plp.ks-plp--filter-open,
html.ks-desktop-site-mobile .ks-plp.ks-plp--filter-open,
.ks-plp.ks-plp--filter-open {
    overflow-x: visible !important;
}
html.ks-touch-nav .ks-plp.ks-plp--filter-open .ks-plp-inner,
html.ks-desktop-site-mobile .ks-plp.ks-plp--filter-open .ks-plp-inner,
.ks-plp.ks-plp--filter-open .ks-plp-inner,
html.ks-touch-nav .ks-plp.ks-plp--filter-open .ks-plp-main,
html.ks-desktop-site-mobile .ks-plp.ks-plp--filter-open .ks-plp-main,
.ks-plp.ks-plp--filter-open .ks-plp-main {
    overflow-x: visible !important;
}
html.ks-touch-nav .ks-plp .ks-plp-filters-scroll.is-dropdown-open,
html.ks-desktop-site-mobile .ks-plp .ks-plp-filters-scroll.is-dropdown-open,
.ks-plp .ks-plp-filters-scroll.is-dropdown-open {
    overflow: visible !important;
    z-index: 500;
    position: relative;
}
html.ks-touch-nav .ks-plp .ks-plp-side,
html.ks-desktop-site-mobile .ks-plp .ks-plp-side {
    display: none !important;
}
html.ks-touch-nav .ks-plp .ks-plp-split,
html.ks-desktop-site-mobile .ks-plp .ks-plp-split {
    grid-template-columns: 1fr !important;
}
html.ks-touch-nav .ks-plp .ks-plp-head,
html.ks-desktop-site-mobile .ks-plp .ks-plp-head {
    display: none !important;
}
html.ks-touch-nav .ks-plp .ks-plp-topbar--desktop,
html.ks-desktop-site-mobile .ks-plp .ks-plp-topbar--desktop,
.ks-plp .ks-plp-topbar--desktop {
    display: inline-flex !important;
}
html.ks-touch-nav .ks-plp .ks-plp-topbar--mobile,
html.ks-desktop-site-mobile .ks-plp .ks-plp-topbar--mobile {
    display: none !important;
}
html.ks-touch-nav .ks-plp .ks-plp-mobile-sticky-bar,
html.ks-desktop-site-mobile .ks-plp .ks-plp-mobile-sticky-bar {
    display: block !important;
}
html.ks-touch-nav .ks-plp .ks-plp-mobile-plp-tools,
html.ks-desktop-site-mobile .ks-plp .ks-plp-mobile-plp-tools {
    display: flex !important;
}
html.ks-touch-nav .ks-plp .ks-plp-brands-desktop,
html.ks-desktop-site-mobile .ks-plp .ks-plp-brands-desktop {
    display: flex !important;
}

/* ثبّت 3 أعمدة في وضع سطح المكتب على الجوال (بدون وميض 4 ثم 3) */
html.ks-desktop-site-mobile .ks-plp.ks-plp--has-sidebar .ks-plp-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
}
html.ks-desktop-site-mobile .ks-plp:not(.ks-plp--has-sidebar) .ks-plp-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
}

/* Mobile PLP sheets (Teleport → body) — global so fixed/z-index always apply */
html.ks-plp-mob-overlay-open #app {
    position: relative;
    z-index: 0;
}
html.ks-plp-mob-overlay-open .ks-plp-card,
html.ks-plp-mob-overlay-open .ks-plp-card-img-wrap,
html.ks-plp-mob-overlay-open .ks-plp-grid,
html.ks-plp-mob-overlay-open .ks-plp-mobile-sticky-bar {
    pointer-events: none;
}
.ks-mob-backdrop {
    position: fixed;
    inset: 0;
    z-index: 11000 !important;
    background: rgba(0, 0, 0, 0.55) !important;
    overscroll-behavior: contain;
    display: flex;
    justify-content: flex-start;
    align-items: stretch;
    touch-action: none;
    transform: translateZ(0);
    -webkit-transform: translateZ(0);
}
.ks-mob-backdrop--hub,
.ks-mob-backdrop--detail {
    justify-content: center;
    align-items: flex-end;
    padding: 0;
}
.ks-mob-drawer {
    width: min(90vw, 440px);
    background: #fff;
    color: #111;
    box-shadow: 6px 0 28px rgba(0, 0, 0, 0.18);
    display: flex;
    flex-direction: column;
    outline: none;
}
.ks-mob-hub-sheet,
.ks-mob-detail-sheet {
    width: 100%;
    max-width: 100%;
    background: #fff;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 -14px 40px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 2;
}
.ks-mob-hub-sheet {
    max-height: min(92dvh, 100%);
    min-height: min(72dvh, 100%);
    border-radius: 16px 16px 0 0;
    padding-bottom: env(safe-area-inset-bottom, 0px);
}
.ks-mob-detail-sheet {
    max-height: min(92dvh, 100%);
    min-height: min(72dvh, 100%);
    border-radius: 16px 16px 0 0;
    padding-bottom: env(safe-area-inset-bottom, 0px);
}
.ks-mob-drawer[dir="rtl"],
.ks-mob-hub-sheet[dir="rtl"],
.ks-mob-detail-sheet[dir="rtl"] {
    direction: rtl;
    text-align: right;
}
.ks-mob-detail-sheet[dir="rtl"] .ks-mob-detail-head {
    padding-inline: calc(14px + env(safe-area-inset-right)) 12px;
}
.ks-mob-detail-sheet[dir="rtl"] .ks-mob-detail-back {
    transform: none;
}
.ks-mob-detail-sheet[dir="ltr"] .ks-mob-detail-back {
    transform: none;
}
.ks-mob-detail-sheet[dir="rtl"] .ks-mob-detail-title {
    text-align: right;
}
.ks-mob-hub-sheet[dir="rtl"] .ks-mob-hub-row,
.ks-mob-detail-sheet[dir="rtl"] .ks-mob-radio-row,
.ks-mob-detail-sheet[dir="rtl"] .ks-mob-check-row {
    text-align: right;
}
</style>

<template>
    <AuthenticatedLayout>
        <section class="container flex flex-col mx-auto mt-4 px-4 pb-8">
            <Head :title="`Shelf · ${shelf.name}`" />

            <div class="mb-4">
                <Link
                    :href="route('locations.index')"
                    class="text-sm text-blue-600 hover:underline"
                >
                    ← Back to locations
                </Link>
            </div>

            <div class="shelf-header">
                <div>
                    <h1 class="shelf-title">{{ shelf.name }}</h1>

                    <div class="location-path mt-3">
                        <template
                            v-for="(part, index) in locationParts"
                            :key="`${part.label}-${index}`"
                        >
                            <span class="location-path__item">
                                <i :class="part.icon"></i>
                                <span>{{ part.label }}</span>
                            </span>

                            <span
                                v-if="index < locationParts.length - 1"
                                class="location-path__separator"
                            >
                                ›
                            </span>
                        </template>
                    </div>
                </div>

                <div class="shelf-actions">
                    <div class="shelf-count">📚 {{ books.length }} books</div>

                    <Link
                        :href="route('books.create', shelf.id)"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    >
                        Add book
                    </Link>
                </div>
            </div>

            <div class="results w-full mt-8">
                <div v-if="books.length === 0" class="text-gray-500 py-4">
                    No books found on this shelf.
                </div>
                <DataTable
                    v-if="books.length"
                    :value="books"
                    v-model:filters="filters"
                    filterDisplay="row"
                    responsiveLayout="scroll"
                    paginator
                    :rows="rows"
                    :first="first"
                    @page="onPage"
                    :rowsPerPageOptions="[10, 25, 50, 100]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    currentPageReportTemplate="From {first} to {last} of {totalRecords}"
                    sortField="title"
                    :sortOrder="1"
                >
                    <Column field="img" header="Cover">
                        <template #body="slotProps">
                            <div class="cover">
                                <button
                                    v-if="slotProps.data.img"
                                    type="button"
                                    class="cover__link cover__button"
                                    @click="
                                        openImageModal(
                                            slotProps.data.img,
                                            slotProps.data.title,
                                        )
                                    "
                                >
                                    <img
                                        class="copertina"
                                        :src="slotProps.data.img"
                                        :alt="`Cover of ${slotProps.data.title}`"
                                        loading="lazy"
                                        @error="failedImage($event)"
                                    />

                                    <span class="cover__overlay">Preview</span>
                                </button>

                                <div v-else class="cover__placeholder">
                                    <img
                                        class="copertina"
                                        src="/storage/no_book_cover.png"
                                        :alt="`No cover available for ${slotProps.data.title}`"
                                        loading="lazy"
                                    />

                                    <span
                                        class="cover__overlay cover__overlay--static"
                                    >
                                        No cover
                                    </span>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="title"
                        header="Title"
                        :sortable="true"
                        :showFilterMenu="false"
                    >
                        <template #body="slotProps">
                            <Link
                                :href="
                                    route('book.show', {
                                        id: slotProps.data.id,
                                        source: 'shelf-books',
                                        shelf_id: shelf.id,
                                        page: Math.floor(first / rows) + 1,
                                        rows: rows,
                                    })
                                "
                                class="text-blue-600 hover:underline"
                            >
                                {{ slotProps.data.title }}
                            </Link>
                        </template>

                        <template #filter="{ filterModel, filterCallback }">
                            <input
                                v-model="filterModel.value"
                                type="text"
                                class="border rounded px-2 py-1 w-full"
                                placeholder="Filter title"
                                @input="filterCallback()"
                            />
                        </template>
                    </Column>

                    <Column header="Authors">
                        <template #body="slotProps">
                            <span
                                v-for="(author, index) in slotProps.data
                                    .authors"
                                :key="`${author.name}-${author.surname}-${index}`"
                                :title="author.name + ' ' + author.surname"
                            >
                                {{ author.name }} {{ author.surname }}
                                <span
                                    v-if="
                                        index <
                                        slotProps.data.authors.length - 1
                                    "
                                >
                                    ,
                                </span>
                            </span>
                        </template>
                    </Column>

                    <Column
                        field="publisher"
                        header="Publisher"
                        :sortable="true"
                        :showFilterMenu="false"
                    >
                        <template #filter="{ filterModel, filterCallback }">
                            <input
                                v-model="filterModel.value"
                                type="text"
                                class="border rounded px-2 py-1 w-full"
                                placeholder="Filter publisher"
                                @input="filterCallback()"
                            />
                        </template>
                    </Column>
                    <Column
                        field="isbn"
                        header="ISBN"
                        :sortable="true"
                        :showFilterMenu="false"
                    >
                        <template #filter="{ filterModel, filterCallback }">
                            <input
                                v-model="filterModel.value"
                                type="text"
                                class="border rounded px-2 py-1 w-full"
                                placeholder="Filter ISBN"
                                @input="filterCallback()"
                            />
                        </template>
                    </Column>
                    <Column field="pages" header="Pages" :sortable="true" />
                </DataTable>
            </div>
            <Teleport to="body">
                <div
                    v-if="previewImage"
                    class="image-modal"
                    @click.self="closeImageModal"
                >
                    <div class="image-modal__content">
                        <button
                            type="button"
                            class="image-modal__close"
                            @click="closeImageModal"
                        >
                            ×
                        </button>

                        <div class="image-modal__title">
                            {{ previewTitle }}
                        </div>

                        <img
                            :src="previewImage"
                            :alt="previewTitle || 'Book cover preview'"
                            class="image-modal__img"
                        />
                    </div>
                </div>
            </Teleport>
        </section>
    </AuthenticatedLayout>
</template>

<script>
import { Head, Link } from "@inertiajs/inertia-vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

export default {
    name: "ShelfBooks",

    components: {
        Head,
        Link,
        AuthenticatedLayout,
    },

    props: {
        shelf: {
            type: Object,
            required: true,
        },
        books: {
            type: Array,
            required: true,
        },
    },

    data() {
        return {
            rows: 10,
            first: 0,
            previewImage: null,
            previewTitle: "",
            filters: {
                title: { value: null, matchMode: "contains" },
                publisher: { value: null, matchMode: "contains" },
                isbn: { value: null, matchMode: "contains" },
            },
        };
    },
    computed: {
        locationParts() {
            return this.getLocationParts(this.shelf.location);
        },
    },

    mounted() {
        const params = new URLSearchParams(window.location.search);
        const page = parseInt(params.get("page") || "1", 10);
        const rows = parseInt(params.get("rows") || "10", 10);

        this.rows = Number.isNaN(rows) || rows < 1 ? 10 : rows;
        this.first =
            Number.isNaN(page) || page < 1 ? 0 : (page - 1) * this.rows;
    },

    methods: {
        failedImage(event) {
            event.target.src = "/storage/no_book_cover.png";
            event.target.alt = "Cover not available";
        },

        openImageModal(image, title) {
            this.previewImage = image;
            this.previewTitle = title ?? "";
        },

        closeImageModal() {
            this.previewImage = null;
            this.previewTitle = "";
        },

        updateQueryString(page, rows) {
            const url = new URL(window.location.href);

            url.searchParams.set("page", String(page));
            url.searchParams.set("rows", String(rows));

            window.history.replaceState({}, "", url.toString());
        },

        onPage(event) {
            this.first = event.first;
            this.rows = event.rows;

            const page = Math.floor(event.first / event.rows) + 1;
            this.updateQueryString(page, event.rows);
        },

        getLocationParts(location) {
            const building = location?.building ?? "";
            const room = location?.room ?? "";
            const bookshelf = location?.bookshelf ?? "";
            const shelf = location?.shelf ?? "";

            const parts = [];

            if (building) parts.push({ icon: "pi pi-home", label: building });
            if (room) parts.push({ icon: "pi pi-stop", label: room });
            if (bookshelf) {
                parts.push({ icon: "pi pi-server", label: bookshelf });
            }
            if (shelf) parts.push({ icon: "pi pi-book", label: shelf });

            return parts;
        },
    },
};
</script>

<style lang="scss" scoped>
.shelf-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.shelf-title {
    font-size: 1.9rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.shelf-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.75rem;
}

.shelf-count {
    font-size: 0.9rem;
    font-weight: 600;
    color: #6b7280;
}

.location-path {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.35rem;
    line-height: 1.4;
}

.location-path__item {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: #374151;
}

.location-path__separator {
    color: #9ca3af;
}

.cover {
    width: 72px;
}

.cover__button {
    border: 0;
    padding: 0;
    cursor: pointer;
    background: transparent;
    appearance: none;
    -webkit-appearance: none;
}

.cover__link,
.cover__placeholder {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 108px;
    border-radius: 8px;
    overflow: hidden;
    background: #f3f4f6;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    transition:
        transform 0.18s ease,
        box-shadow 0.18s ease;
}

.cover__link:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 22px rgba(0, 0, 0, 0.18);
}

.copertina {
    width: 72px;
    height: 108px;
    object-fit: cover;
    display: block;
    transition:
        transform 0.18s ease,
        filter 0.18s ease;
}

.cover__link:hover .copertina {
    transform: scale(1.04);
    filter: brightness(0.72);
}

.cover__overlay {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    padding: 0.28rem 0.55rem;
    border-radius: 9999px;
    background: rgba(17, 24, 39, 0.82);
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    opacity: 0;
    transition: opacity 0.18s ease;
    pointer-events: none;
    white-space: nowrap;
}

.cover__link:hover .cover__overlay {
    opacity: 1;
}

.cover__overlay--static {
    opacity: 1;
    background: rgba(55, 65, 81, 0.82);
    font-size: 0.65rem;
    max-width: 60px;
    text-align: center;
    white-space: normal;
    line-height: 1.15;
    padding: 0.3rem 0.4rem;
}

:deep(.p-paginator) {
    margin-top: 1rem;
}
:deep(.p-column-filter) {
    width: 100%;
}

.image-modal {
    position: fixed;
    inset: 0;
    background: rgba(17, 24, 39, 0.72);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    z-index: 9999;
}

.image-modal__content {
    position: relative;
    max-width: 520px;
    width: 100%;
    background: #ffffff;
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.22);
}

.image-modal__close {
    position: absolute;
    top: 0.65rem;
    right: 0.8rem;
    border: 0;
    background: transparent;
    font-size: 1.8rem;
    line-height: 1;
    cursor: pointer;
    color: #6b7280;
}

.image-modal__title {
    margin-bottom: 1rem;
    padding-right: 2rem;
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
}

.image-modal__img {
    display: block;
    width: 100%;
    max-height: 75vh;
    object-fit: contain;
    border-radius: 10px;
    background: #f3f4f6;
}
</style>

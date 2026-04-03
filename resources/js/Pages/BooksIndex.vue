<template>
    <AuthenticatedLayout>
        <section class="container flex flex-col mx-auto mt-4">
            <Head title="Cerca libri" />

            <div class="search w-full columns-1">
                <label for="cerca">Cerca: </label>
                <input
                    id="cerca"
                    v-model="searchTxt"
                    type="text"
                    placeholder="Titolo, autore o ISBN..."
                    class="border rounded px-3 py-1"
                />
                <span v-if="isLoading" class="ml-3 text-sm text-gray-500">
                    Cerco...
                </span>
            </div>

            <div class="results w-full columns-1 mt-24">
                <DataTable
                    v-if="results.length"
                    :value="results"
                    v-model:filters="filters"
                    responsiveLayout="scroll"
                    filterDisplay="row"
                    paginator
                    :rows="10"
                    :rowsPerPageOptions="[10, 25, 50, 100]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    currentPageReportTemplate="Da {first} a {last} di {totalRecords}"
                    sortMode="multiple"
                >
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
                                placeholder="Filtra ISBN"
                                @input="filterCallback()"
                            />
                        </template>
                    </Column>

                    <Column field="img" header="Copertina">
                        <template #body="slotProps">
                            <div class="cover">
                                <a
                                    v-if="slotProps.data.img"
                                    :href="slotProps.data.img"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="cover__link"
                                >
                                    <img
                                        class="copertina"
                                        :src="slotProps.data.img"
                                        :alt="`Copertina di ${slotProps.data.title}`"
                                        loading="lazy"
                                        @error="failedImage($event)"
                                    />

                                    <span class="cover__overlay"> Apri </span>
                                </a>

                                <div v-else class="cover__placeholder">
                                    <img
                                        class="copertina"
                                        src="/storage/no_book_cover.png"
                                        :alt="`Copertina non disponibile per ${slotProps.data.title}`"
                                        loading="lazy"
                                    />

                                    <span
                                        class="cover__overlay cover__overlay--static"
                                    >
                                        Nessuna copertina
                                    </span>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column
                        field="title"
                        header="Titolo"
                        :sortable="true"
                        sortField="title"
                        :showFilterMenu="false"
                    >
                        <template #body="slotProps">
                            <Link
                                :href="
                                    route('book.show', {
                                        id: slotProps.data.id,
                                        source: 'books',
                                        q: searchTxt.trim() || undefined,
                                    })
                                "
                                class="text-blue-600 hover:underline"
                            >
                                <span
                                    v-html="
                                        highlightMatch(slotProps.data.title)
                                    "
                                />
                            </Link>
                        </template>

                        <template #filter="{ filterModel, filterCallback }">
                            <input
                                v-model="filterModel.value"
                                type="text"
                                class="border rounded px-2 py-1 w-full"
                                placeholder="Filtra titolo"
                                @input="filterCallback()"
                            />
                        </template>
                    </Column>

                    <Column header="Autori">
                        <template #body="slotProps">
                            <span
                                v-for="(author, index) in slotProps.data
                                    .authors"
                                :key="`${author.id ?? author.name}-${author.surname}-${index}`"
                            >
                                <span
                                    v-html="
                                        highlightMatch(
                                            `${author.name} ${author.surname}`,
                                        )
                                    "
                                />
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
                        header="Casa editrice"
                        :sortable="true"
                        :showFilterMenu="false"
                    >
                        <template #filter="{ filterModel, filterCallback }">
                            <input
                                v-model="filterModel.value"
                                type="text"
                                class="border rounded px-2 py-1 w-full"
                                placeholder="Filtra editore"
                                @input="filterCallback()"
                            />
                        </template>
                    </Column>
                    <Column field="pages" header="Pagine" :sortable="true" />

                    <Column header="Percorso" :sortable="true">
                        <template #body="slotProps">
                            <div class="location-path">
                                <template
                                    v-for="(part, index) in getLocationParts(
                                        slotProps.data,
                                    )"
                                    :key="`${part.label}-${index}`"
                                >
                                    <span class="location-path__item">
                                        <i :class="part.icon"></i>
                                        <span>{{ part.label }}</span>
                                    </span>

                                    <span
                                        v-if="
                                            index <
                                            getLocationParts(slotProps.data)
                                                .length -
                                                1
                                        "
                                        class="location-path__separator"
                                    >
                                        →
                                    </span>
                                </template>
                            </div>
                        </template>
                    </Column>

                    <Column header="Link">
                        <template #body="slotProps">
                            <Link
                                v-if="getShelfId(slotProps.data)"
                                :href="
                                    route(
                                        'books.create',
                                        getShelfId(slotProps.data),
                                    )
                                "
                                class="text-blue-600 hover:underline"
                            >
                                Vai al ripiano
                            </Link>

                            <span v-else class="text-gray-400">
                                Ripiano non disponibile
                            </span>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script>
import axios from "axios";
import { Head, Link } from "@inertiajs/inertia-vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

export default {
    name: "BooksIndex",

    components: {
        Link,
        Head,
        AuthenticatedLayout,
    },

    data() {
        return {
            searchTxt: "",
            results: [],
            isLoading: false,
            searchTimeout: null,
            filters: {
                isbn: { value: null, matchMode: "contains" },
                title: { value: null, matchMode: "contains" },
                publisher: { value: null, matchMode: "contains" },
            },
        };
    },

    watch: {
        searchTxt(newValue) {
            clearTimeout(this.searchTimeout);

            const query = newValue.trim();
            if (!query) {
                this.results = [];
                this.updateQueryString("");
                return;
            }

            this.searchTimeout = setTimeout(() => {
                this.search();
            }, 400);
        },
    },
    mounted() {
        const query = new URLSearchParams(window.location.search).get("q");

        if (query) {
            this.searchTxt = query;
        }
    },

    methods: {
        failedImage(event) {
            event.target.src = "/storage/no_book_cover.png";
            event.target.alt = "Copertina non disponibile";
            event.target.classList.add("copertina");
        },
        updateQueryString(query) {
            const url = new URL(window.location.href);

            if (query) {
                url.searchParams.set("q", query);
            } else {
                url.searchParams.delete("q");
            }

            window.history.replaceState({}, "", url.toString());
        },
        highlightMatch(text) {
            const query = this.searchTxt.trim();

            if (!text) return "";
            if (!query) return String(text);

            const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
            const regex = new RegExp(`(${escapedQuery})`, "ig");

            return String(text).replace(
                regex,
                '<mark class="search-highlight">$1</mark>',
            );
        },
        getShelfId(item) {
            return item?.location?.shelf_id ?? null;
        },
        getLocationParts(item) {
            const building = item?.location?.building ?? "";
            const room = item?.location?.room ?? "";
            const bookshelf = item?.location?.bookshelf ?? "";
            const shelf = item?.location?.shelf ?? "";

            const parts = [];

            if (building) parts.push({ icon: "pi pi-home", label: building });
            if (room) parts.push({ icon: "pi pi-stop", label: room });
            if (bookshelf)
                parts.push({ icon: "pi pi-server", label: bookshelf });
            if (shelf) parts.push({ icon: "pi pi-book", label: shelf });

            return parts;
        },
        search() {
            clearTimeout(this.searchTimeout);

            const query = this.searchTxt.trim();

            if (!query) {
                this.$toast.add({
                    severity: "warn",
                    summary: "Ricerca",
                    detail: "Inserisci titolo, ISBN o autore",
                    life: 3000,
                });

                this.results = [];
                this.updateQueryString("");
                return;
            }

            this.updateQueryString(query);

            const path = route("api.books.search");

            this.isLoading = true;

            axios
                .get(path, {
                    params: {
                        searchtxt: query,
                    },
                })
                .then((r) => {
                    const data = r.data.books ?? r.data.data ?? r.data ?? [];

                    if (!data.length) {
                        this.$toast?.add({
                            severity: "info",
                            summary: "Ricerca",
                            detail: "Nessun libro trovato",
                            life: 3000,
                        });
                    }

                    this.results = data;
                })
                .catch((error) => {
                    console.error("Errore ricerca:", error);

                    this.$toast?.add({
                        severity: "error",
                        summary: "Errore ricerca",
                        detail: "Errore durante la ricerca dei libri",
                        life: 4000,
                    });

                    this.results = [];
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
    },
    beforeUnmount() {
        clearTimeout(this.searchTimeout);
    },
};
</script>

<style lang="scss" scoped>
.p-tree {
    border: 0;
    padding: 0;
}

.cover {
    width: 72px;
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
:deep(.search-highlight) {
    background: #fef08a;
    color: inherit;
    padding: 0 0.12rem;
    border-radius: 3px;
}
:deep(.p-paginator) {
    margin-top: 1rem;
}

:deep(.p-column-filter) {
    width: 100%;
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
</style>

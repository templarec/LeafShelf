<template>
    <AuthenticatedLayout>
        <section class="page-shell container mx-auto px-4 py-6">
            <Head title="Autori" />

            <div class="page-header">
                <div>
                    <h1 class="page-title">Autori</h1>
                    <p class="page-subtitle">
                        Cerca gli autori presenti nel catalogo e visualizza i
                        libri associati.
                    </p>
                </div>

                <div class="page-links">
                    <Link :href="route('dashboard')" class="page-link">
                        ← Dashboard
                    </Link>
                    <Link :href="route('books.index')" class="page-link">
                        ← Libri
                    </Link>
                </div>
            </div>

            <div class="card-base search-card">
                <div class="search-toolbar">
                    <div class="search-input-wrap">
                        <label for="cerca" class="search-label"
                            >Cerca autore</label
                        >
                        <input
                            id="cerca"
                            v-model="searchTxt"
                            @keyup.enter="search"
                            type="text"
                            class="search-input"
                            placeholder="Nome o cognome"
                        />
                    </div>

                    <button
                        @click="search"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150"
                    >
                        Cerca
                    </button>
                </div>
            </div>

            <div class="card-base results-card">
                <div class="results-head">
                    <h2 class="results-title">Risultati</h2>
                    <span class="results-count"
                        >{{ results.length }} autori</span
                    >
                </div>

                <div v-if="results.length" class="results-table-wrap">
                    <DataTable
                        :value="results"
                        responsiveLayout="scroll"
                        v-model:expandedRows="expandedRows"
                        dataKey="id"
                        paginator
                        :rows="10"
                        :rowsPerPageOptions="[10, 25, 50]"
                        currentPageReportTemplate="Da {first} a {last} di {totalRecords}"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    >
                        <Column :expander="true" style="width: 56px" />
                        <Column
                            field="surname"
                            header="Cognome"
                            :sortable="true"
                        />
                        <Column field="name" header="Nome" :sortable="true" />
                        <Column header="Libri">
                            <template #body="slotProps">
                                {{ slotProps.data.books?.length ?? 0 }}
                            </template>
                        </Column>

                        <template #expansion="slotProps">
                            <div class="books-panel">
                                <div class="books-panel__head">
                                    <h3>
                                        Libri di {{ slotProps.data.name }}
                                        {{ slotProps.data.surname }}
                                    </h3>
                                    <span>
                                        {{ slotProps.data.books?.length ?? 0 }}
                                        libri
                                    </span>
                                </div>

                                <DataTable
                                    class="p-datatable-sm"
                                    :value="slotProps.data.books ?? []"
                                    responsiveLayout="scroll"
                                    paginator
                                    :rows="5"
                                    :rowsPerPageOptions="[5, 10, 20]"
                                >
                                    <Column
                                        field="isbn"
                                        header="ISBN"
                                        sortable
                                    />
                                    <Column
                                        field="title"
                                        header="Titolo"
                                        sortable
                                    />
                                    <Column
                                        field="publisher"
                                        header="Casa editrice"
                                        sortable
                                    />
                                    <Column
                                        field="pages"
                                        header="Pagine"
                                        sortable
                                    />
                                </DataTable>
                            </div>
                        </template>
                    </DataTable>
                </div>

                <div v-else class="empty-state">Nessun autore trovato.</div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script>
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/inertia-vue3";

export default {
    name: "AuthorsIndex",

    components: {
        Head,
        Link,
        AuthenticatedLayout,
    },

    data() {
        return {
            searchTxt: "",
            results: [],
            expandedRows: [],
            isLoading: false,
        };
    },

    mounted() {
        this.search();
    },

    methods: {
        search() {
            const path = route("api.authors.search");

            this.isLoading = true;

            axios
                .get(path, {
                    params: {
                        searchtxt: this.searchTxt,
                    },
                })
                .then((r) => {
                    this.results =
                        r.data.authors ?? r.data.data ?? r.data ?? [];
                    this.expandedRows = [];
                })
                .catch((err) => {
                    console.log(err);
                    this.results = [];
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
    },
};
</script>

<style lang="scss" scoped>
.page-shell {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.page-title {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
}

.page-subtitle {
    margin-top: 0.45rem;
    color: #6b7280;
    max-width: 760px;
}

.page-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.page-link {
    text-decoration: underline;
    color: #2563eb;
    font-size: 0.95rem;
}

.page-link:hover {
    color: #b91c1c;
}

.card-base {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    padding: 1.1rem;
}

.search-toolbar {
    display: flex;
    gap: 1rem;
    align-items: end;
    flex-wrap: wrap;
}

.search-input-wrap {
    flex: 1 1 340px;
}

.search-label {
    display: block;
    margin-bottom: 0.45rem;
    font-weight: 600;
    color: #374151;
}

.search-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 0.75rem 0.9rem;
    background: #fff;
    color: #111827;
}

.search-input:focus {
    outline: none;
    border-color: #60a5fa;
    box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.14);
}

.results-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.results-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #111827;
}

.results-count {
    font-size: 0.9rem;
    color: #6b7280;
    font-weight: 600;
}

.books-panel {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 1rem;
}

.books-panel__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.9rem;
    flex-wrap: wrap;
}

.books-panel__head h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
}

.books-panel__head span {
    font-size: 0.88rem;
    color: #6b7280;
    font-weight: 600;
}

.empty-state {
    color: #6b7280;
    font-size: 0.95rem;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background: #f9fafb;
}

:deep(.p-paginator) {
    margin-top: 1rem;
}
</style>

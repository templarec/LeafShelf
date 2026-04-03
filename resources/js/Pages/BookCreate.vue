<template>
    <AuthenticatedLayout>
        <Head title="Inserisci libri" />

        <section class="page-shell container mx-auto px-4 py-6">
            <Toast />

            <div class="page-header">
                <div>
                    <h1 class="page-title">Inserisci libro</h1>
                    <p class="page-subtitle">
                        Scansiona il barcode ISBN, controlla i dati e salva il
                        libro nel ripiano selezionato.
                    </p>
                </div>

                <div class="page-links">
                    <Link :href="route('dashboard')" class="page-link">
                        ← Dashboard
                    </Link>
                    <Link :href="route('locations.index')" class="page-link">
                        ← Gestione posizioni
                    </Link>
                </div>
            </div>

            <div class="insert-layout">
                <div class="left-column">
                    <aside class="position-card card-base">
                        <div class="card-head">
                            <h2 class="card-title">Posizione selezionata</h2>
                            <p class="card-text">
                                I prossimi libri verranno inseriti in questo
                                percorso.
                            </p>
                        </div>

                        <!-- Compact breadcrumb is more useful here than a full tree:
                             this page is operational, so the user mainly needs confirmation
                             of the current target shelf, not full navigation. -->
                        <div
                            v-if="locationParts.length"
                            class="location-breadcrumb"
                        >
                            <template
                                v-for="(part, index) in locationParts"
                                :key="part.key"
                            >
                                <span
                                    class="location-breadcrumb__item"
                                    :class="{
                                        'location-breadcrumb__item--active':
                                            part.key === currentShelfKey,
                                    }"
                                >
                                    <i :class="part.icon"></i>
                                    <span>{{ part.label }}</span>
                                </span>

                                <span
                                    v-if="index < locationParts.length - 1"
                                    class="location-breadcrumb__separator"
                                >
                                    →
                                </span>
                            </template>
                        </div>

                        <p v-else class="empty-hint">
                            Posizione non disponibile.
                        </p>
                    </aside>

                    <div class="recent-card card-base">
                        <div class="card-head">
                            <h2 class="card-title">Ultimi libri inseriti</h2>
                            <p class="card-text">
                                Ultimi inserimenti utili come riferimento
                                rapido.
                            </p>
                        </div>

                        <!-- Kept as a separate component:
                             easier to evolve independently (pagination / polling / links). -->
                        <LastBooks />
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-card card-base">
                        <div class="card-head">
                            <h2 class="card-title">Dati libro</h2>
                            <p class="card-text">
                                Lo scanner può scrivere l'ISBN e inviare Enter
                                in modo automatico.
                            </p>
                        </div>

                        <div class="book-form-grid">
                            <div class="form-row form-row--isbn isbn-focus">
                                <label
                                    :class="errors.isbn ? 'errors' : ''"
                                    for="isbn"
                                >
                                    ISBN
                                    <span class="isbn-badge"
                                        >scanner pronto</span
                                    >
                                </label>

                                <div>
                                    <input
                                        class="field-input field-input--isbn"
                                        type="text"
                                        id="isbn"
                                        @keyup.enter="checkISBN"
                                        v-model="book.isbn"
                                        :disabled="isCheckingIsbn || isSaving"
                                        placeholder="Scansiona o inserisci ISBN"
                                    />

                                    <p v-if="isCheckingIsbn" class="field-hint">
                                        Recupero dati libro...
                                    </p>
                                </div>
                            </div>

                            <div class="form-row">
                                <label for="titolo">Titolo</label>
                                <input
                                    class="field-input"
                                    type="text"
                                    id="titolo"
                                    v-model="book.title"
                                    @keyup.enter="handlePrimaryEnter"
                                    placeholder="Titolo del libro"
                                />
                            </div>

                            <div class="form-row form-row--authors">
                                <label
                                    :class="errors.authors ? 'errors' : ''"
                                    for="authors"
                                >
                                    Autori
                                </label>

                                <div class="authors-panel">
                                    <div
                                        v-if="authors.length"
                                        id="authors"
                                        class="authors-list"
                                    >
                                        <div
                                            v-for="(author, index) in authors"
                                            :key="`${author.name}-${author.surname}-${index}`"
                                            class="author-chip"
                                        >
                                            <span
                                                >{{ author.name }}
                                                {{ author.surname }}</span
                                            >
                                            <button
                                                type="button"
                                                class="author-chip__remove"
                                                @click="removeAuthor(index)"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>

                                    <p v-else class="empty-hint">
                                        Nessun autore presente.
                                    </p>

                                    <div class="manual-author-grid">
                                        <input
                                            class="field-input"
                                            type="text"
                                            id="author-name"
                                            v-model="newAuthorName"
                                            placeholder="Nome"
                                        />
                                        <input
                                            class="field-input"
                                            type="text"
                                            id="author-surname"
                                            v-model="newAuthorSurname"
                                            placeholder="Cognome"
                                            @keyup.enter="addAuthor"
                                        />
                                    </div>

                                    <div>
                                        <Button
                                            v-if="canAddAuthor"
                                            @click="addAuthor"
                                            label="Aggiungi autore"
                                            icon="pi pi-plus"
                                            class="p-button-success"
                                        ></Button>
                                        <Button
                                            v-else
                                            label="Aggiungi autore"
                                            icon="pi pi-plus"
                                            class="p-button-success p-disabled"
                                        ></Button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <label for="casaedi">Casa editrice</label>
                                <input
                                    class="field-input"
                                    type="text"
                                    id="casaedi"
                                    v-model="book.publisher"
                                    @keyup.enter="handlePrimaryEnter"
                                    placeholder="Casa editrice"
                                />
                            </div>

                            <div class="form-row">
                                <label for="pagine">Pagine</label>
                                <input
                                    class="field-input"
                                    type="text"
                                    id="pagine"
                                    v-model="book.pages"
                                    @keyup.enter="handlePrimaryEnter"
                                    placeholder="Numero pagine"
                                />
                            </div>
                        </div>

                        <div class="actions-row">
                            <!-- Save button is enabled only when the minimum required data
                                 is present, which keeps scanner-based workflow fast and predictable. -->
                            <Button
                                v-if="canSaveBook"
                                id="save-book-btn"
                                @click="saveBook"
                                label="Salva"
                                icon="pi pi-check"
                                class="p-button-success"
                                :disabled="isSaving || isCheckingIsbn"
                            ></Button>
                            <Button
                                v-else
                                label="Salva"
                                icon="pi pi-check"
                                class="p-button-success p-disabled"
                            ></Button>
                        </div>
                    </div>
                </div>

                <aside class="cover-card card-base">
                    <div class="card-head">
                        <h2 class="card-title">Copertina</h2>
                        <p class="card-text">
                            Anteprima del libro recuperato dall'ISBN.
                        </p>
                    </div>

                    <div v-if="book.img" class="cover-preview">
                        <img :src="book.img" alt="Copertina libro" />
                    </div>

                    <div v-else class="cover-empty">
                        Nessuna copertina disponibile.
                    </div>
                </aside>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script>
import { Head, Link } from "@inertiajs/inertia-vue3";
import axios from "axios";

import LastBooks from "@/Components/LastBooks.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

export default {
    name: "BookCreate",

    components: {
        AuthenticatedLayout,
        Link,
        LastBooks,
        Head,
    },

    data() {
        return {
            // Normalized frontend state:
            // keep the payload shape aligned with backend DTOs (`name`, `surname`, etc.)
            // to avoid special-case mapping on create/update.
            authors: [],
            book: {
                isbn: null,
                title: null,
                publisher: null,
                img: null,
                pages: null,
            },

            // Shelf context is inferred from route parameter and rendered as breadcrumb.
            idShelf: null,
            currentShelfKey: null,
            locationTree: [],

            // Temporary fields used for manual author insertion.
            newAuthorName: null,
            newAuthorSurname: null,

            // Simple field-level UI errors: good enough here, avoids overengineering.
            errors: {
                isbn: false,
                title: false,
                authors: false,
            },

            isCheckingIsbn: false,
            isSaving: false,
        };
    },

    mounted() {
        this.focusIsbn();

        const pathParts = window.location.pathname.split("/").filter(Boolean);
        const lastPathPart = pathParts[pathParts.length - 1] ?? null;

        // Defensive route guard:
        // this page only makes sense when opened for a specific shelf.
        if (!/^\d+$/.test(String(lastPathPart))) {
            this.$toast.add({
                severity: "warn",
                summary: "Ripiano mancante",
                detail: "Apri questa pagina da un ripiano selezionato in Gestione posizioni",
                life: 3000,
            });

            setTimeout(() => {
                window.location.href = route("locations.index");
            }, 400);

            return;
        }

        this.idShelf = String(lastPathPart);
        this.currentShelfKey = `shelf-${this.idShelf}`;

        const url = route("api.books.location");

        axios
            .get(url, {
                params: {
                    idShelf: this.idShelf,
                },
            })
            .then((r) => {
                const rawTree = r.data?.data ?? null;

                // The backend may return either a single node or an array.
                // Normalizing here keeps the template/computed layer simpler.
                this.locationTree = Array.isArray(rawTree)
                    ? rawTree
                    : rawTree
                      ? [rawTree]
                      : [];

                if (!this.locationTree.length) {
                    throw new Error("Empty location tree payload");
                }
            })
            .catch((err) => {
                console.log(err);

                this.$toast.add({
                    severity: "error",
                    summary: "Errore caricamento ripiano",
                    detail: "Impossibile caricare la posizione selezionata",
                    life: 3500,
                });
            });
    },

    computed: {
        canAddAuthor() {
            return !!(
                this.newAuthorName?.trim() && this.newAuthorSurname?.trim()
            );
        },

        canSaveBook() {
            return !!(
                this.normalizeIsbn(this.book.isbn) &&
                this.book.title?.trim() &&
                this.authors.length > 0
            );
        },

        // Convert the nested location tree into a flat breadcrumb for UI rendering.
        // This is intentionally view-oriented and kept out of the backend payload shape.
        locationParts() {
            const parts = [];
            let current = Array.isArray(this.locationTree)
                ? this.locationTree[0]
                : null;

            while (current) {
                parts.push({
                    key: current.key,
                    label: current.label,
                    icon: current.icon,
                });

                current = current.children?.[0] ?? null;
            }

            return parts;
        },
    },

    methods: {
        // Accept scanner noise such as spaces / dashes and normalize early.
        normalizeIsbn(value) {
            return String(value ?? "")
                .replace(/[^0-9Xx]/g, "")
                .toUpperCase()
                .trim();
        },

        // Reset only the form state, not the shelf context.
        // This allows fast repeated insertion into the same shelf.
        resetBookForm() {
            this.authors = [];
            this.book = {
                isbn: null,
                title: null,
                publisher: null,
                img: null,
                pages: null,
            };
            this.newAuthorName = null;
            this.newAuthorSurname = null;
            this.errors = {
                isbn: false,
                title: false,
                authors: false,
            };
        },

        focusIsbn() {
            this.$nextTick(() => {
                document.getElementById("isbn")?.focus();
            });
        },

        focusField(id) {
            this.$nextTick(() => {
                document.getElementById(id)?.focus();
            });
        },

        // After lookup, move the user to the first meaningful missing field.
        // This keeps the page efficient for barcode-driven data entry.
        focusNextAfterLookup() {
            if (!this.book.title?.trim()) {
                this.focusField("titolo");
                return;
            }

            if (!this.authors.length) {
                this.focusField("author-name");
                return;
            }

            if (!this.book.publisher?.trim()) {
                this.focusField("casaedi");
                return;
            }

            if (!this.book.pages) {
                this.focusField("pagine");
                return;
            }

            this.focusField("save-book-btn");
        },

        // Enter on secondary fields should save only when the record is valid.
        // This avoids accidental submissions with partial data.
        handlePrimaryEnter() {
            if (this.isCheckingIsbn || this.isSaving) {
                return;
            }

            if (this.canSaveBook) {
                this.saveBook();
            }
        },

        addAuthor() {
            const name = this.newAuthorName?.trim() ?? "";
            const surname = this.newAuthorSurname?.trim() ?? "";

            if (!name || !surname) {
                return;
            }

            // Prevent duplicate authors at UI level before hitting the backend.
            const alreadyExists = this.authors.some(
                (author) =>
                    author.name.toLowerCase() === name.toLowerCase() &&
                    author.surname.toLowerCase() === surname.toLowerCase(),
            );

            if (alreadyExists) {
                this.$toast.add({
                    severity: "info",
                    summary: "Autore già presente",
                    detail: "Questo autore è già nella lista",
                    life: 2200,
                });
                return;
            }

            this.authors.push({
                name,
                surname,
            });

            this.newAuthorName = "";
            this.newAuthorSurname = "";
        },

        removeAuthor(index) {
            this.authors.splice(index, 1);
        },

        async checkISBN() {
            const normalizedIsbn = this.normalizeIsbn(this.book.isbn);

            if (!normalizedIsbn) {
                this.$toast.add({
                    severity: "warn",
                    summary: "ISBN mancante",
                    detail: "Scansiona o inserisci un ISBN valido",
                    life: 2500,
                });
                return;
            }

            this.resetBookForm();
            this.book.isbn = normalizedIsbn;
            this.isCheckingIsbn = true;

            const path = route("api.isbn.lookup");

            try {
                const res = await axios.get(path, {
                    params: {
                        isbn: normalizedIsbn,
                    },
                });

                const payload = res.data?.[0] ?? null;

                if (!payload || payload.errorMessage || !payload.book) {
                    this.$toast.add({
                        severity: "info",
                        summary: "Libro non trovato",
                        detail: "Non trovato online",
                        life: 2500,
                    });
                    this.focusField("titolo");
                    return;
                }

                const book = payload.book;

                this.book.title = book.title_long || book.title || null;
                this.book.publisher = book.publisher || null;
                this.book.img = book.image || null;
                this.book.pages = book.pages || null;

                this.authors = [];

                // Keep a permissive normalization strategy for third-party metadata:
                // external APIs can be inconsistent, so basic deduplication is done client-side.
                (book.authors || []).forEach((item) => {
                    const parts = String(item)
                        .replace(",", "")
                        .trim()
                        .split(/\s+/);

                    const surname = parts.shift() ?? "";
                    const name = parts.join(" ");

                    const author = {
                        name: name.trim(),
                        surname: surname.trim(),
                    };

                    const alreadyExists = this.authors.some(
                        (a) =>
                            a.name === author.name &&
                            a.surname === author.surname,
                    );

                    if (!alreadyExists && (author.name || author.surname)) {
                        this.authors.push(author);
                    }
                });

                this.$toast.add({
                    severity: "success",
                    summary: "Libro trovato",
                    detail: "Campi compilati automaticamente",
                    life: 1800,
                });

                this.focusNextAfterLookup();
            } catch (err) {
                console.log(err);

                this.$toast.add({
                    severity: "error",
                    summary: "Errore ISBN",
                    detail: "Errore durante il recupero del libro",
                    life: 3000,
                });
            } finally {
                this.isCheckingIsbn = false;
            }
        },

        async saveBook() {
            this.errors.isbn = false;
            this.errors.title = false;
            this.errors.authors = false;

            this.book.isbn = this.normalizeIsbn(this.book.isbn);

            if (!this.book.isbn) {
                this.errors.isbn = true;
                this.$toast.add({
                    severity: "warn",
                    summary: "ISBN mancante",
                    detail: "Inserisci o scansiona un ISBN",
                    life: 2500,
                });
                return;
            }

            if (!this.book.title) {
                this.errors.title = true;
                this.$toast.add({
                    severity: "warn",
                    summary: "Titolo mancante",
                    detail: "Il titolo è obbligatorio",
                    life: 2500,
                });
                return;
            }

            if (!this.authors.length) {
                this.errors.authors = true;
                this.$toast.add({
                    severity: "warn",
                    summary: "Autore mancante",
                    detail: "Aggiungi almeno un autore",
                    life: 2500,
                });
                return;
            }

            this.isSaving = true;

            try {
                const path = route("api.books.store");

                const r = await axios.post(path, {
                    isbn: this.book.isbn,
                    title: this.book.title,
                    publisher: this.book.publisher,
                    pages: this.book.pages,
                    shelfId: this.idShelf,
                    img: this.book.img,
                    authors: this.authors,
                });

                if (r.status === 200) {
                    this.$toast.add({
                        severity: "success",
                        summary: "Salvato",
                        detail: "Libro inserito",
                        life: 1800,
                    });

                    // UX choice:
                    // after a successful save, reset and focus ISBN again to optimize
                    // batch insertion with a barcode scanner.
                    this.resetBookForm();
                    this.focusIsbn();
                }
            } catch (err) {
                console.log(err);

                this.$toast.add({
                    severity: "error",
                    summary: "Errore salvataggio",
                    detail: "Impossibile salvare il libro",
                    life: 3000,
                });
            } finally {
                this.isSaving = false;
            }
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
    max-width: 680px;
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

.insert-layout {
    display: grid;
    grid-template-columns: minmax(260px, 1.05fr) minmax(0, 2fr) minmax(
            240px,
            0.95fr
        );
    gap: 1.25rem;
    align-items: start;
    min-height: calc(100vh - 210px);
}

.position-card,
.recent-card,
.form-card,
.cover-card {
    min-width: 0;
    align-self: stretch;
}

.card-base {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    padding: 1.1rem;
}

.card-head {
    margin-bottom: 1rem;
}

.card-title {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 700;
    color: #111827;
}

.card-text {
    margin-top: 0.35rem;
    color: #6b7280;
    font-size: 0.92rem;
    line-height: 1.45;
}

.form-column {
    min-width: 0;
    display: flex;
    min-height: 0;
}

.left-column {
    display: grid;
    grid-template-rows: auto minmax(0, 1fr);
    gap: 1rem;
    min-height: 0;
}

.form-card {
    display: flex;
    flex-direction: column;
    width: 100%;
    min-height: 0;
}

.book-form-grid {
    display: grid;
    gap: 1rem;
    min-height: 0;
}

.form-row {
    display: grid;
    grid-template-columns: 170px 1fr;
    gap: 1rem;
    align-items: start;
}

.form-row label {
    padding-top: 0.8rem;
    font-weight: 600;
    color: #374151;
    line-height: 1.2;
}

.form-row--authors {
    align-items: start;
}

.field-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 0.75rem 0.9rem;
    background: #fff;
    color: #111827;
}

.field-input:focus {
    outline: none;
    border-color: #60a5fa;
    box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.14);
}

.field-hint {
    margin-top: 0.45rem;
    font-size: 0.86rem;
    color: #6b7280;
}

.actions-row {
    display: flex;
    justify-content: flex-end;
    margin-top: 1.25rem;
    padding-top: 1rem;
}

.errors {
    color: #dc2626;
}

.authors-panel {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.authors-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.author-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.38rem 0.65rem;
    border-radius: 9999px;
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    font-size: 0.9rem;
}

.author-chip__remove {
    border: 0;
    background: transparent;
    color: #065f46;
    cursor: pointer;
    font-size: 1rem;
    line-height: 1;
    padding: 0;
}

.manual-author-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem;
}

.location-breadcrumb {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem;
    line-height: 1.45;
}

.location-breadcrumb__item {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.5rem;
    border-radius: 8px;
    color: #374151;
    background: #f9fafb;
}

.location-breadcrumb__item--active {
    font-weight: 700;
    color: #b91c1c;
    background: #fee2e2;
}

.location-breadcrumb__separator {
    color: #9ca3af;
}

.cover-preview {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 320px;
    background: #f8fafc;
    border: 1px dashed #d1d5db;
    border-radius: 14px;
    padding: 1rem;
}

.cover-card {
    position: sticky;
    top: 1rem;
    min-width: 0;
    align-self: start;
    min-height: 0;
}

.cover-preview img {
    max-width: 100%;
    max-height: 420px;
    object-fit: contain;
    display: block;
    border-radius: 10px;
}

.cover-empty,
.empty-hint {
    color: #6b7280;
    font-size: 0.92rem;
}

.recent-card {
    min-width: 0;
    min-height: 0;
    overflow: hidden;
}

@media (max-width: 1200px) {
    .insert-layout {
        grid-template-columns: 1fr;
        min-height: auto;
    }

    .left-column,
    .form-column,
    .cover-card {
        align-self: auto;
    }

    .cover-card {
        position: static;
    }

    .cover-preview {
        min-height: 220px;
    }
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 0.45rem;
    }

    .form-row label {
        padding-top: 0;
    }

    .manual-author-grid {
        grid-template-columns: 1fr;
    }
}

/* ISBN visual focus:
   this field is the primary interaction target for scanner-based usage. */
.isbn-focus label {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.field-input--isbn {
    font-size: 1.1rem;
    font-weight: 600;
    letter-spacing: 0.03em;
}

.isbn-badge {
    margin-left: 8px;
    font-size: 0.75rem;
    background: #10b981;
    color: white;
    padding: 2px 6px;
    border-radius: 6px;
    font-weight: 600;
}

.field-input--isbn:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.15);
}
</style>

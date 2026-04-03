<template>
    <AuthenticatedLayout>
        <!-- Page header stays outside the main content card area:
             it keeps navigation visually stable across pages like BookCreate / AuthorsIndex. -->
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ book.title }}</h1>
                <p class="page-subtitle">
                    Scheda libro con metadati, autori e posizione nel catalogo.
                </p>
            </div>

            <div class="page-links">
                <Link :href="backUrl" class="page-link">
                    {{ backLabel }}
                </Link>
            </div>
        </div>

        <section class="page-shell container mx-auto px-4 py-6">
            <Head :title="book.title" />

            <div class="book-actions">
                <button
                    v-if="!isEditing"
                    type="button"
                    class="book-button book-button--primary"
                    @click="startEditing"
                >
                    Modifica
                </button>

                <template v-else>
                    <button
                        type="button"
                        class="book-button book-button--primary"
                        :disabled="isSaving"
                        @click="saveBook"
                    >
                        <span v-if="isSaving">Salvataggio...</span>
                        <span v-else>Salva</span>
                    </button>

                    <button
                        type="button"
                        class="book-button book-button--secondary"
                        :disabled="isSaving"
                        @click="cancelEditing"
                    >
                        Annulla
                    </button>
                </template>
            </div>

            <div class="book-layout">
                <aside class="cover-card card-base">
                    <div class="card-head">
                        <h2 class="card-title">Copertina</h2>
                        <p class="card-text">
                            Anteprima del libro presente nel catalogo.
                        </p>
                    </div>

                    <div v-if="book.img" class="cover-preview">
                        <img
                            :src="book.img"
                            :alt="book.title"
                            @error="failedImage($event)"
                        />
                    </div>

                    <div v-else class="cover-empty">
                        Nessuna copertina disponibile.
                    </div>
                </aside>

                <div class="details-column">
                    <div class="details-card card-base">
                        <div class="card-head">
                            <h2 class="card-title">Dettagli libro</h2>
                            <p class="card-text">
                                Informazioni principali del libro selezionato.
                            </p>
                        </div>

                        <div class="details-grid">
                            <div class="detail-row">
                                <span class="detail-label">Titolo</span>

                                <div v-if="isEditing" class="book-field">
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        class="book-input"
                                    />
                                </div>

                                <span
                                    v-else
                                    class="detail-value detail-value--title"
                                >
                                    {{ book.title }}
                                </span>
                            </div>

                            <div class="detail-row detail-row--authors">
                                <span class="detail-label">Autori</span>

                                <!-- Authors are editable only in local form state.
                                     This avoids mutating the source `book` prop before the save succeeds. -->
                                <div v-if="isEditing" class="authors-editor">
                                    <div
                                        v-if="form.authors.length"
                                        class="authors-list"
                                    >
                                        <span
                                            v-for="(
                                                author, index
                                            ) in form.authors"
                                            :key="`${author.name}-${author.surname}-${index}`"
                                            class="author-chip"
                                        >
                                            {{ author.name }}
                                            {{ author.surname }}
                                            <button
                                                type="button"
                                                class="author-chip__remove"
                                                @click="removeAuthor(index)"
                                            >
                                                ×
                                            </button>
                                        </span>
                                    </div>

                                    <span
                                        v-if="!form.authors.length"
                                        class="detail-value"
                                    >
                                        —
                                    </span>

                                    <div class="authors-editor__inputs">
                                        <input
                                            v-model="newAuthorName"
                                            type="text"
                                            class="book-input"
                                            placeholder="Nome"
                                        />
                                        <input
                                            v-model="newAuthorSurname"
                                            type="text"
                                            class="book-input"
                                            placeholder="Cognome"
                                            @keyup.enter="addAuthor"
                                        />
                                    </div>

                                    <div>
                                        <button
                                            type="button"
                                            class="book-button book-button--secondary"
                                            @click="addAuthor"
                                        >
                                            Aggiungi autore
                                        </button>
                                    </div>
                                </div>

                                <div v-else class="authors-list">
                                    <span
                                        v-for="(author, index) in book.authors"
                                        :key="
                                            author.id ??
                                            `${author.name}-${author.surname}-${index}`
                                        "
                                        class="author-chip"
                                    >
                                        {{ author.name }} {{ author.surname }}
                                    </span>

                                    <span
                                        v-if="!book.authors?.length"
                                        class="detail-value"
                                    >
                                        —
                                    </span>
                                </div>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">ISBN</span>

                                <input
                                    v-if="isEditing"
                                    v-model="form.isbn"
                                    type="text"
                                    class="book-input"
                                />

                                <span v-else class="detail-value">
                                    {{ book.isbn || "—" }}
                                </span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Casa editrice</span>

                                <input
                                    v-if="isEditing"
                                    v-model="form.publisher"
                                    type="text"
                                    class="book-input"
                                />

                                <span v-else class="detail-value">
                                    {{ book.publisher || "—" }}
                                </span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Pagine</span>

                                <input
                                    v-if="isEditing"
                                    v-model="form.pages"
                                    type="number"
                                    min="1"
                                    class="book-input"
                                />

                                <span v-else class="detail-value">
                                    {{ book.pages || "—" }}
                                </span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Immagine</span>

                                <input
                                    v-if="isEditing"
                                    v-model="form.img"
                                    type="text"
                                    class="book-input"
                                />

                                <span
                                    v-else
                                    class="detail-value detail-value--break"
                                >
                                    {{ book.img || "—" }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="location-card card-base">
                        <div class="card-head">
                            <h2 class="card-title">Posizione</h2>
                            <p class="card-text">
                                Percorso completo del libro nella libreria.
                            </p>
                        </div>

                        <!-- Breadcrumb-like rendering is enough here:
                             the page needs context and navigation clarity, not a full interactive tree. -->
                        <div class="location-breadcrumb">
                            <span
                                v-if="book.location?.building"
                                class="location-breadcrumb__item"
                            >
                                <i class="pi pi-home"></i>
                                <span>{{ book.location.building }}</span>
                            </span>

                            <span
                                v-if="book.location?.room"
                                class="location-breadcrumb__separator"
                            >
                                →
                            </span>
                            <span
                                v-if="book.location?.room"
                                class="location-breadcrumb__item"
                            >
                                <i class="pi pi-stop"></i>
                                <span>{{ book.location.room }}</span>
                            </span>

                            <span
                                v-if="book.location?.bookshelf"
                                class="location-breadcrumb__separator"
                            >
                                →
                            </span>
                            <span
                                v-if="book.location?.bookshelf"
                                class="location-breadcrumb__item"
                            >
                                <i class="pi pi-server"></i>
                                <span>{{ book.location.bookshelf }}</span>
                            </span>

                            <span
                                v-if="book.location?.shelf"
                                class="location-breadcrumb__separator"
                            >
                                →
                            </span>
                            <span
                                v-if="book.location?.shelf"
                                class="location-breadcrumb__item location-breadcrumb__item--active"
                            >
                                <i class="pi pi-book"></i>
                                <span>{{ book.location.shelf }}</span>
                            </span>
                        </div>

                        <div class="location-actions">
                            <Link
                                v-if="book.location?.shelf_id"
                                :href="
                                    route(
                                        'books.create',
                                        book.location.shelf_id,
                                    )
                                "
                                class="page-link"
                            >
                                Vai al ripiano
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script>
import axios from "axios";
import { Head, Link } from "@inertiajs/inertia-vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

export default {
    name: "BookShow",

    components: {
        Head,
        Link,
        AuthenticatedLayout,
    },

    props: {
        // The server provides the canonical book snapshot.
        // Editing is performed on a local copy to keep prop usage predictable.
        book: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            isEditing: false,
            isSaving: false,

            // Local editable copy:
            // this avoids directly mutating the incoming prop and makes cancel/reset trivial.
            form: {
                id: this.book.id,
                isbn: this.book.isbn ?? "",
                title: this.book.title ?? "",
                publisher: this.book.publisher ?? "",
                pages: this.book.pages ?? "",
                img: this.book.img ?? "",
                authors: (this.book.authors ?? []).map((author) => ({
                    name: author.name ?? "",
                    surname: author.surname ?? "",
                })),
            },

            // Temporary fields for author insertion in edit mode.
            newAuthorName: "",
            newAuthorSurname: "",
        };
    },

    computed: {
        backUrl() {
            const params = new URLSearchParams(window.location.search);
            const source = params.get("source");

            // Preserve navigation context:
            // if the user came from a shelf page, bring them back there with pagination state.
            if (source === "shelf-books") {
                const shelfId = params.get("shelf_id");
                const page = params.get("page");
                const rows = params.get("rows");

                if (!shelfId) {
                    return route("locations.index");
                }

                const url = new URL(
                    route("shelf.books.index", shelfId),
                    window.location.origin,
                );

                if (page) {
                    url.searchParams.set("page", page);
                }

                if (rows) {
                    url.searchParams.set("rows", rows);
                }

                return `${url.pathname}${url.search}`;
            }

            // Otherwise return to the books search page, preserving the query when present.
            const query = params.get("q");

            if (!query) {
                return route("books.index");
            }

            const url = new URL(route("books.index"), window.location.origin);
            url.searchParams.set("q", query);

            return `${url.pathname}${url.search}`;
        },

        backLabel() {
            const params = new URLSearchParams(window.location.search);
            const source = params.get("source");

            if (source === "shelf-books") {
                return "← Torna al ripiano";
            }

            return "← Torna alla ricerca";
        },
    },

    methods: {
        failedImage(event) {
            event.target.src = "/storage/no_book_cover.png";
            event.target.alt = "Copertina non disponibile";
        },

        startEditing() {
            // Rebuild the form from the latest saved book state.
            // This keeps edit mode idempotent even after multiple save/cancel cycles.
            this.form = {
                id: this.book.id,
                isbn: this.book.isbn ?? "",
                title: this.book.title ?? "",
                publisher: this.book.publisher ?? "",
                pages: this.book.pages ?? "",
                img: this.book.img ?? "",
                authors: (this.book.authors ?? []).map((author) => ({
                    name: author.name ?? "",
                    surname: author.surname ?? "",
                })),
            };

            this.newAuthorName = "";
            this.newAuthorSurname = "";

            this.isEditing = true;
        },

        cancelEditing() {
            // Reset the local draft to the last persisted state.
            this.form = {
                id: this.book.id,
                isbn: this.book.isbn ?? "",
                title: this.book.title ?? "",
                publisher: this.book.publisher ?? "",
                pages: this.book.pages ?? "",
                img: this.book.img ?? "",
                authors: (this.book.authors ?? []).map((author) => ({
                    name: author.name ?? "",
                    surname: author.surname ?? "",
                })),
            };

            this.newAuthorName = "";
            this.newAuthorSurname = "";

            this.isEditing = false;
        },

        addAuthor() {
            const name = this.newAuthorName?.trim() ?? "";
            const surname = this.newAuthorSurname?.trim() ?? "";

            if (!name || !surname) {
                return;
            }

            // Lightweight client-side deduplication:
            // prevents noisy duplicates before the request even reaches the backend.
            const alreadyExists = this.form.authors.some(
                (author) =>
                    author.name.toLowerCase() === name.toLowerCase() &&
                    author.surname.toLowerCase() === surname.toLowerCase(),
            );

            if (alreadyExists) {
                return;
            }

            this.form.authors.push({ name, surname });
            this.newAuthorName = "";
            this.newAuthorSurname = "";
        },

        removeAuthor(index) {
            this.form.authors.splice(index, 1);
        },

        async saveBook() {
            // Minimal client-side validation:
            // the backend remains the source of truth, but obvious failures are stopped early.
            if (!this.form.title?.trim()) {
                this.$toast?.add({
                    severity: "warn",
                    summary: "Validation",
                    detail: "Title is required",
                    life: 3000,
                });
                return;
            }

            this.isSaving = true;

            try {
                await axios.put(route("book.update", this.book.id), {
                    isbn: this.form.isbn,
                    title: this.form.title,
                    publisher: this.form.publisher,
                    pages: this.form.pages || null,
                    img: this.form.img,
                    authors: this.form.authors,
                });

                // Update the local displayed book after a successful save
                // so the page stays consistent without a full reload.
                this.book.isbn = this.form.isbn;
                this.book.title = this.form.title;
                this.book.publisher = this.form.publisher;
                this.book.pages = this.form.pages;
                this.book.img = this.form.img;
                this.book.authors = this.form.authors.map((author) => ({
                    name: author.name,
                    surname: author.surname,
                }));

                this.isEditing = false;

                this.$toast?.add({
                    severity: "success",
                    summary: "Book updated",
                    detail: "Changes saved successfully",
                    life: 3000,
                });
            } catch (error) {
                console.error("Book update error:", error);

                this.$toast?.add({
                    severity: "error",
                    summary: "Update failed",
                    detail: "Could not save book changes",
                    life: 4000,
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

.book-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.book-layout {
    display: grid;
    grid-template-columns: minmax(240px, 0.9fr) minmax(0, 2fr);
    gap: 1.25rem;
    align-items: start;
}

.details-column {
    display: grid;
    gap: 1rem;
    min-width: 0;
}

.cover-card,
.details-card,
.location-card {
    min-width: 0;
}

/* Sticky cover is useful on large screens:
   the image remains visible while scanning the rest of the metadata. */
.cover-card {
    position: sticky;
    top: 1rem;
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

.details-grid {
    display: grid;
    gap: 1rem;
}

.detail-row {
    display: grid;
    grid-template-columns: 160px 1fr;
    gap: 1rem;
    align-items: start;
}

.detail-label {
    font-weight: 600;
    color: #374151;
    padding-top: 0.75rem;
}

.detail-value {
    color: #111827;
    padding-top: 0.75rem;
}

.detail-value--title {
    font-size: 1.15rem;
    font-weight: 700;
    padding-top: 0.45rem;
}

.detail-value--break {
    word-break: break-all;
}

.detail-row--authors {
    align-items: start;
}

.authors-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.authors-editor {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.authors-editor__inputs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
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

.location-actions {
    margin-top: 1rem;
}

.book-button {
    border: 0;
    border-radius: 8px;
    padding: 0.7rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background 0.18s ease,
        opacity 0.18s ease;
}

.book-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.book-button--primary {
    background: #111827;
    color: #fff;
}

.book-button--primary:hover:not(:disabled) {
    background: #1f2937;
}

.book-button--secondary {
    background: #e5e7eb;
    color: #111827;
}

.book-button--secondary:hover:not(:disabled) {
    background: #d1d5db;
}

.book-field {
    display: grid;
    gap: 0.4rem;
}

.book-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 0.75rem 0.9rem;
    background: #fff;
    color: #111827;
}

.book-input:focus {
    outline: none;
    border-color: #60a5fa;
    box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.14);
}

@media (max-width: 1024px) {
    .book-layout {
        grid-template-columns: 1fr;
    }

    .cover-card {
        position: static;
    }

    .cover-preview {
        min-height: 240px;
    }
}

@media (max-width: 640px) {
    .detail-row {
        grid-template-columns: 1fr;
        gap: 0.4rem;
    }

    .detail-label,
    .detail-value {
        padding-top: 0;
    }

    .authors-editor__inputs {
        grid-template-columns: 1fr;
    }
}
</style>

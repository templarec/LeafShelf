<template>
    <section class="last-books">
        <div v-if="lastBooks.length" class="last-books__list">
            <article
                v-for="book in lastBooks"
                :key="book.id ?? `${book.isbn}-${book.title}`"
                class="last-books__item"
            >
                <div class="last-books__meta">
                    <span class="last-books__label">ISBN</span>
                    <span class="last-books__isbn">{{ book.isbn || "—" }}</span>
                </div>

                <div class="last-books__title">
                    {{ book.title || "Titolo non disponibile" }}
                </div>
            </article>
        </div>

        <p v-else class="last-books__empty">
            Nessun libro inserito di recente.
        </p>
    </section>
</template>

<script>
import axios from "axios";

export default {
    name: "LastBooks",

    data() {
        return {
            lastBooks: [],
        };
    },

    mounted() {
        const path = route("api.books.recent");

        axios.get(path).then((r) => {
            this.lastBooks = r.data.books ?? r.data.data ?? r.data ?? [];
        });
    },
};
</script>

<style lang="scss" scoped>
.last-books {
    min-width: 0;
}

.last-books__list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;

    max-height: 260px;
    overflow-y: auto;
    padding-right: 4px;
}

.last-books__item {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #f9fafb;
    padding: 0.85rem 0.95rem;
    transition:
        background 0.18s ease,
        border-color 0.18s ease;
}

.last-books__item:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.last-books__meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
    font-size: 0.84rem;
}

.last-books__label {
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.last-books__isbn {
    color: #374151;
    word-break: break-all;
}

.last-books__title {
    font-size: 0.98rem;
    font-weight: 600;
    color: #111827;
    line-height: 1.35;
}

.last-books__empty {
    color: #6b7280;
    font-size: 0.92rem;
    margin: 0;
}
/* subtle scrollbar for the list */
.last-books__list::-webkit-scrollbar {
    width: 6px;
}

.last-books__list::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 6px;
}

.last-books__list::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>

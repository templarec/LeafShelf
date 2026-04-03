<template>
    <section class="container mx-auto mt-1 space-y-6 px-4">
        <Toast />
        <Head title="Gestione posizioni" />

        <div>
            <Link
                class="text-sm text-gray-600 transition hover:text-gray-900"
                :href="route('dashboard')"
            >
                &lt; Dashboard
            </Link>
        </div>

        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-gray-900">
                Gestione posizioni
            </h1>
            <p class="text-sm text-gray-500">
                Seleziona un nodo dell'alberatura e aggiungi il livello
                successivo direttamente dal pannello contestuale.
            </p>
        </div>

        <div class="layout-grid">
            <div class="tree-panel">
                <div class="panel-head">
                    <div>
                        <h2 class="panel-title">Alberatura posizioni</h2>
                        <p class="panel-subtitle">
                            Clicca un nodo per aggiungere il suo elemento
                            figlio.
                        </p>
                    </div>

                    <div class="panel-actions">
                        <button
                            type="button"
                            class="ghost-button"
                            @click="startFromRoot"
                        >
                            Nuova casa
                        </button>
                        <button
                            v-if="selectedNode"
                            type="button"
                            class="ghost-button"
                            @click="clearSelection"
                        >
                            Deseleziona
                        </button>
                    </div>
                </div>

                <div class="tree-search">
                    <span class="tree-search__icon pi pi-search"></span>
                    <input
                        v-model="treeSearch"
                        type="text"
                        class="tree-search__input"
                        placeholder="Cerca casa, stanza, libreria o ripiano"
                    />
                    <button
                        v-if="treeSearch"
                        type="button"
                        class="tree-search__clear"
                        @click="treeSearch = ''"
                    >
                        Pulisci
                    </button>
                </div>

                <Tree
                    :value="filteredTree"
                    v-model:expandedKeys="expandedKeys"
                    class="tree-view"
                >
                    <template #default="slotProps">
                        <button
                            type="button"
                            class="tree-node-button"
                            :class="{
                                'tree-node-button--active':
                                    selectedNode?.key === slotProps.node.key,
                            }"
                            @click.stop="handleNodeClick(slotProps.node)"
                        >
                            <span class="tree-node-button__label">
                                {{ slotProps.node.label }}
                            </span>
                            <span
                                v-if="showNodeCount(slotProps.node)"
                                class="tree-node-count"
                            >
                                {{ nodeCountLabel(slotProps.node) }}
                            </span>
                        </button>
                    </template>
                </Tree>
            </div>

            <div class="side-column">
                <div ref="contextPanel" class="context-panel">
                    <div class="context-badge">Pannello contestuale</div>
                    <h3 class="context-title">{{ panelTitle }}</h3>
                    <p class="context-text">
                        {{ panelDescription }}
                    </p>

                    <div v-if="selectedPath" class="selected-path">
                        <span class="selected-path__label">Contesto</span>
                        <span class="selected-path__value">{{
                            selectedPath
                        }}</span>
                        <span
                            v-if="selectedNodeBooksCount !== null"
                            class="selected-path__meta"
                        >
                            {{ selectedNodeBooksCountLabel }}
                        </span>
                    </div>
                    <div
                        v-if="selectedType === 'shelf'"
                        class="action-card action-card--warning"
                    >
                        <div
                            class="action-card__title action-card__title--warning"
                        >
                            Rinomina ripiano
                        </div>
                        <p class="action-card__text action-card__text--warning">
                            Modifica il nome del ripiano selezionato senza
                            cambiare la sua posizione.
                        </p>

                        <div class="field-row">
                            <span class="field-icon pi pi-pencil"></span>
                            <input
                                v-model="shelfRename"
                                class="field-input"
                                type="text"
                                placeholder="Nuovo nome ripiano"
                                @keyup.enter="renameSelectedShelf"
                            />
                        </div>

                        <Button
                            :loading="isRenaming"
                            :disabled="!canRenameShelf"
                            label="Rinomina ripiano"
                            icon="pi pi-pencil"
                            class="p-button-warning"
                            @click="renameSelectedShelf"
                        />
                    </div>
                    <div v-if="actionType" class="form-block">
                        <label class="field-label" for="location-name">
                            {{ inputLabel }}
                        </label>
                        <div class="field-row">
                            <span class="field-icon" :class="actionIcon"></span>
                            <input
                                id="location-name"
                                v-model="newItemName"
                                class="field-input"
                                type="text"
                                :placeholder="inputPlaceholder"
                                @keyup.enter="submitCurrent"
                            />
                        </div>

                        <Button
                            :loading="isSubmitting"
                            :disabled="!canSubmit"
                            :label="submitLabel"
                            icon="pi pi-check"
                            class="p-button-success"
                            @click="submitCurrent"
                        />
                    </div>

                    <div
                        v-else-if="selectedType === 'shelf'"
                        class="form-block"
                    >
                        <Button
                            label="Vedi libri del ripiano"
                            icon="pi pi-book"
                            class="p-button-primary"
                            @click="goToShelfBooks"
                        />

                        <Button
                            label="Inserisci libro"
                            icon="pi pi-plus"
                            class="p-button-secondary"
                            @click="goToShelfInsert"
                        />
                    </div>

                    <div v-else class="empty-state">
                        <i class="pi pi-info-circle"></i>
                        <span>
                            Seleziona una casa, una stanza o una libreria per
                            aggiungere il livello successivo, oppure crea una
                            nuova casa dalla radice.
                        </span>
                    </div>
                    <div
                        v-if="selectedNode"
                        class="action-card action-card--danger"
                    >
                        <div
                            class="action-card__title action-card__title--danger"
                        >
                            Rimozione nodo
                        </div>
                        <p class="action-card__text action-card__text--danger">
                            Puoi rimuovere
                            <strong>{{ selectedNode.label }}</strong>
                            dalla gerarchia. L'operazione verrà bloccata se
                            questa rimozione comporta anche l'eliminazione di
                            libri.
                        </p>

                        <div
                            v-if="isDeleteConfirmOpen"
                            class="inline-confirm inline-confirm--danger"
                        >
                            <div class="inline-confirm__text">
                                Confermi la rimozione di
                                <strong>{{ selectedNode.label }}</strong
                                >?
                            </div>

                            <div class="inline-confirm__actions">
                                <Button
                                    type="button"
                                    label="Annulla"
                                    icon="pi pi-times"
                                    class="p-button-text p-button-secondary"
                                    @click="cancelDelete"
                                />

                                <Button
                                    type="button"
                                    :loading="isDeleting"
                                    label="Conferma rimozione"
                                    icon="pi pi-check"
                                    class="p-button-danger"
                                    @click="confirmDeleteSelectedNode"
                                />
                            </div>
                        </div>

                        <Button
                            v-else
                            type="button"
                            :disabled="isDeleting"
                            label="Rimuovi nodo selezionato"
                            icon="pi pi-times"
                            class="p-button-danger"
                            @click="openDeleteConfirm"
                        />
                    </div>
                </div>

                <div class="legend-panel">
                    <h3 class="panel-title">Legenda</h3>
                    <ul class="legend-list">
                        <li><i class="pi pi-home"></i><span>Casa</span></li>
                        <li><i class="pi pi-stop"></i><span>Stanza</span></li>
                        <li>
                            <i class="pi pi-server"></i><span>Libreria</span>
                        </li>
                        <li>
                            <i class="pi pi-book"></i>
                            <span>Ripiano</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import axios from "axios";
import { Head, Link } from "@inertiajs/inertia-vue3";
import Toast from "primevue/toast";

export default {
    name: "LocationsManager",

    components: {
        Head,
        Link,
        Toast,
    },

    data() {
        return {
            tree: [],
            expandedKeys: {},
            selectionKey: null,
            selectedNode: null,
            newItemName: "",
            isSubmitting: false,
            isDeleting: false,
            shelfRename: "",
            isRenaming: false,
            isDeleteConfirmOpen: false,
            treeSearch: "",
        };
    },

    async mounted() {
        await this.getTree();
    },

    computed: {
        selectedType() {
            if (!this.selectedNode?.key) return null;

            if (this.selectedNode.key.startsWith("building-"))
                return "building";
            if (this.selectedNode.key.startsWith("room-")) return "room";
            if (this.selectedNode.key.startsWith("bookshelf-"))
                return "bookshelf";
            if (this.selectedNode.key.startsWith("shelf-")) return "shelf";

            return null;
        },

        actionType() {
            if (!this.selectedNode) return "building";
            if (this.selectedType === "building") return "room";
            if (this.selectedType === "room") return "bookshelf";
            if (this.selectedType === "bookshelf") return "shelf";
            if (this.selectedType === "shelf") return null;

            return null;
        },

        panelTitle() {
            if (!this.selectedNode) return "Nuova casa";
            if (this.selectedType === "building") return "Aggiungi una stanza";
            if (this.selectedType === "room") return "Aggiungi una libreria";
            if (this.selectedType === "bookshelf") return "Aggiungi un ripiano";
            return "Ripiano selezionato";
        },

        panelDescription() {
            if (!this.selectedNode) {
                return "Nessun nodo selezionato: da qui puoi creare una nuova casa alla radice dell'alberatura.";
            }

            if (this.selectedType === "building") {
                return "Hai selezionato una casa. Inserisci il nome della nuova stanza da creare al suo interno.";
            }

            if (this.selectedType === "room") {
                return "Hai selezionato una stanza. Inserisci il nome della nuova libreria da creare al suo interno.";
            }

            if (this.selectedType === "bookshelf") {
                return "Hai selezionato una libreria. Inserisci il nome del nuovo ripiano da creare al suo interno.";
            }

            if (this.selectedType === "shelf") {
                return "Hai selezionato un ripiano finale dell'alberatura. Da qui puoi andare direttamente alla pagina di inserimento libri per questo ripiano oppure rimuoverlo solo se non contiene libri.";
            }

            return "";
        },
        inputLabel() {
            if (this.actionType === "building") return "Nome casa";
            if (this.actionType === "room") return "Nome stanza";
            if (this.actionType === "bookshelf") return "Nome libreria";
            if (this.actionType === "shelf") return "Nome ripiano";
            return "Nome";
        },

        inputPlaceholder() {
            if (this.actionType === "building") return "Es. Udine";
            if (this.actionType === "room") return "Es. Soggiorno";
            if (this.actionType === "bookshelf") return "Es. Libreria nord";
            if (this.actionType === "shelf") return "Es. Primo ripiano";
            return "Inserisci nome";
        },

        submitLabel() {
            if (this.actionType === "building") return "Inserisci casa";
            if (this.actionType === "room") return "Inserisci stanza";
            if (this.actionType === "bookshelf") return "Inserisci libreria";
            if (this.actionType === "shelf") return "Inserisci ripiano";
            return "Inserisci";
        },

        actionIcon() {
            if (this.actionType === "building") return "pi pi-home";
            if (this.actionType === "room") return "pi pi-stop";
            if (this.actionType === "bookshelf") return "pi pi-server";
            if (this.actionType === "shelf") return "pi pi-book";
            return "pi pi-pencil";
        },

        canSubmit() {
            return (
                !!this.actionType &&
                this.newItemName.trim().length > 0 &&
                !this.isSubmitting
            );
        },

        selectedPath() {
            if (!this.selectedNode?.key) return "";

            const path = this.findPathByKey(this.tree, this.selectedNode.key);
            return path.map((node) => node.label).join(" > ");
        },
        canRenameShelf() {
            return (
                this.selectedType === "shelf" &&
                this.shelfRename.trim().length > 0 &&
                !this.isRenaming
            );
        },
        deleteTitle() {
            if (this.selectedType === "building") return "Rimozione casa";
            if (this.selectedType === "room") return "Rimozione stanza";
            if (this.selectedType === "bookshelf") return "Rimozione libreria";
            if (this.selectedType === "shelf") return "Rimozione ripiano";
            return "Rimozione nodo";
        },

        selectedNodeBooksCount() {
            if (!this.selectedNode) return null;

            const count = Number(
                this.selectedNode?.books_count ??
                    this.selectedNode?.booksCount ??
                    this.selectedNode?.data?.books_count ??
                    this.selectedNode?.data?.booksCount,
            );

            return Number.isFinite(count) ? count : null;
        },

        selectedNodeBooksCountLabel() {
            const count = this.selectedNodeBooksCount;
            if (count === null) return "";

            return `${count} libr${count === 1 ? "o" : "i"} nel nodo selezionato`;
        },

        filteredTree() {
            const query = this.treeSearch.trim().toLowerCase();

            if (!query) return this.tree;

            return this.filterTreeNodes(this.tree, query);
        },
    },

    watch: {
        treeSearch(value) {
            const query = value.trim().toLowerCase();

            if (!query) {
                if (this.selectedNode?.key) {
                    this.expandPath(this.selectedNode.key);
                } else {
                    this.expandedKeys = {};
                }
                return;
            }

            const expanded = {};

            const walk = (nodes) => {
                nodes.forEach((node) => {
                    if (node.children?.length) {
                        expanded[node.key] = true;
                        walk(node.children);
                    }
                });
            };

            walk(this.filteredTree);
            this.expandedKeys = expanded;
        },
    },

    methods: {
        async fetchTree() {
            const r = await axios.get(route("api.locations.tree"));
            return r.data.data ?? r.data ?? [];
        },

        async getTree(preserveKey = null) {
            const keyToRestore = preserveKey ?? this.selectedNode?.key ?? null;

            const treeData = await this.fetchTree();

            this.tree = treeData;

            if (keyToRestore) {
                const restoredNode = this.findNodeByKey(
                    this.tree,
                    keyToRestore,
                );
                if (restoredNode) {
                    this.selectedNode = restoredNode;
                    this.selectionKey = restoredNode.key;
                    this.expandedKeys = {};
                    this.expandPath(restoredNode.key);
                    return;
                }
            }

            this.selectedNode = null;
            this.selectionKey = null;
        },

        findNodeByKey(nodes, key) {
            for (const node of nodes) {
                if (node.key === key) return node;
                if (node.children?.length) {
                    const found = this.findNodeByKey(node.children, key);
                    if (found) return found;
                }
            }

            return null;
        },
        filterTreeNodes(nodes, query) {
            return nodes
                .map((node) => {
                    const label = String(node.label ?? "").toLowerCase();
                    const children = node.children?.length
                        ? this.filterTreeNodes(node.children, query)
                        : [];

                    if (label.includes(query) || children.length) {
                        return {
                            ...node,
                            children,
                        };
                    }

                    return null;
                })
                .filter(Boolean);
        },

        findPathByKey(nodes, key, trail = []) {
            for (const node of nodes) {
                const nextTrail = [...trail, node];

                if (node.key === key) {
                    return nextTrail;
                }

                if (node.children?.length) {
                    const found = this.findPathByKey(
                        node.children,
                        key,
                        nextTrail,
                    );
                    if (found.length) return found;
                }
            }

            return [];
        },
        expandPath(key) {
            const path = this.findPathByKey(this.tree, key);
            const expanded = {};

            path.forEach((node) => {
                expanded[node.key] = true;
            });

            this.expandedKeys = expanded;
        },
        scrollToContextPanel() {
            this.$nextTick(() => {
                this.$refs.contextPanel?.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            });
        },
        showNodeCount(node) {
            const count =
                node?.books_count ??
                node?.booksCount ??
                node?.data?.books_count ??
                node?.data?.booksCount;

            return Number.isFinite(Number(count));
        },

        nodeCountLabel(node) {
            return Number(
                node?.books_count ??
                    node?.booksCount ??
                    node?.data?.books_count ??
                    node?.data?.booksCount ??
                    0,
            );
        },

        handleNodeClick(node) {
            this.selectedNode = node;
            this.selectionKey = node.key;
            this.expandPath(node.key);
            this.newItemName = "";
            this.shelfRename = node?.key?.startsWith("shelf-")
                ? node.label
                : "";
            this.isDeleteConfirmOpen = false;
            this.scrollToContextPanel();
        },
        clearSelection() {
            this.isDeleteConfirmOpen = false;
            this.selectedNode = null;
            this.selectionKey = null;
            this.newItemName = "";
            this.shelfRename = "";
            this.expandedKeys = {};
        },

        startFromRoot() {
            this.clearSelection();
        },
        async renameSelectedShelf() {
            if (this.selectedType !== "shelf" || !this.selectedNode?.id) return;
            if (!this.shelfRename.trim()) return;

            this.isRenaming = true;

            try {
                await axios.patch(route("api.shelves.rename"), {
                    id: this.selectedNode.id,
                    name: this.shelfRename.trim(),
                });

                await this.getTree(`shelf-${this.selectedNode.id}`);
                this.shelfRename = this.shelfRename.trim();
                this.$toast.add({
                    severity: "success",
                    summary: "Ripiano aggiornato",
                    detail: "Ripiano rinominato correttamente",
                    life: 3000,
                });
            } catch (err) {
                this.$toast.add({
                    severity: "error",
                    summary: "Errore",
                    detail:
                        err.response?.data?.message ??
                        "Impossibile rinominare questo ripiano.",
                    life: 4000,
                });
            } finally {
                this.isRenaming = false;
            }
        },
        async deleteSelectedNode() {
            if (!this.selectedNode?.id || !this.selectedNode?.key) return;

            const model = this.getModelFromKey(this.selectedNode.key);
            if (!model) return;

            this.isDeleting = true;

            try {
                await axios.delete(route("api.locations.delete"), {
                    params: {
                        model,
                        id: this.selectedNode.id,
                    },
                });

                await this.getTree();
                this.newItemName = "";
                this.isDeleteConfirmOpen = false;

                this.$toast.add({
                    severity: "success",
                    summary: "Elemento rimosso",
                    detail: "Nodo eliminato correttamente",
                    life: 3000,
                });
            } catch (err) {
                this.isDeleteConfirmOpen = false;

                this.$toast.add({
                    severity: "error",
                    summary: "Errore",
                    detail:
                        err.response?.data?.message ??
                        "Impossibile rimuovere questo elemento.",
                    life: 4000,
                });
            } finally {
                this.isDeleting = false;
            }
        },
        getModelFromKey(key) {
            if (key?.startsWith("building-")) return "App\\Models\\Building";
            if (key?.startsWith("room-")) return "App\\Models\\Room";
            if (key?.startsWith("bookshelf-")) return "App\\Models\\Bookshelf";
            if (key?.startsWith("shelf-")) return "App\\Models\\Shelf";
            return null;
        },

        goToShelfBooks() {
            if (!this.selectedNode?.id || this.selectedType !== "shelf") return;

            window.location.href = route(
                "shelf.books.index",
                this.selectedNode.id,
            );
        },

        // pagina inserimento libro
        goToShelfInsert() {
            if (!this.selectedNode?.id || this.selectedType !== "shelf") return;

            window.location.href = route("books.create", this.selectedNode.id);
        },
        getSubmitConfig() {
            if (this.actionType === "building") {
                return {
                    path: route("api.buildings.store"),
                    params: {
                        name: this.newItemName.trim(),
                    },
                    responseKey: "building",
                    successMessage: "Casa inserita correttamente!",
                };
            }

            if (this.actionType === "room") {
                return {
                    path: route("api.rooms.store"),
                    params: {
                        name: this.newItemName.trim(),
                        building_id: this.selectedNode.id,
                    },
                    responseKey: "room",
                    successMessage: "Stanza inserita correttamente!",
                };
            }

            if (this.actionType === "bookshelf") {
                return {
                    path: route("api.bookshelves.store"),
                    params: {
                        name: this.newItemName.trim(),
                        room_id: this.selectedNode.id,
                    },
                    responseKey: "bookshelf",
                    successMessage: "Libreria inserita correttamente!",
                };
            }

            if (this.actionType === "shelf") {
                return {
                    path: route("api.shelves.store"),
                    params: {
                        name: this.newItemName.trim(),
                        bookshelf_id: this.selectedNode.id,
                    },
                    responseKey: "shelf",
                    successMessage: "Ripiano inserito correttamente!",
                };
            }

            return null;
        },

        async submitCurrent() {
            if (!this.canSubmit) return;

            const config = this.getSubmitConfig();
            if (!config) return;

            this.isSubmitting = true;

            try {
                const r = await axios.post(config.path, config.params);

                const createdId = r.data?.[config.responseKey]?.id ?? null;
                const currentNodeKey = this.selectedNode?.key ?? null;
                const nextKey =
                    this.actionType === "shelf"
                        ? currentNodeKey
                        : createdId
                          ? `${config.responseKey}-${createdId}`
                          : currentNodeKey;

                await this.getTree(nextKey);
                this.newItemName = "";
                this.$toast.add({
                    severity: "success",
                    summary: "Operazione completata",
                    detail: config.successMessage,
                    life: 3000,
                });
            } catch (err) {
                this.$toast.add({
                    severity: "error",
                    summary: "Errore",
                    detail:
                        err.response?.data?.message ??
                        "Impossibile completare l'operazione.",
                    life: 4000,
                });
            } finally {
                this.isSubmitting = false;
            }
        },
        openDeleteConfirm() {
            if (!this.selectedNode || this.isDeleting) return;
            this.isDeleteConfirmOpen = true;
        },

        cancelDelete() {
            if (this.isDeleting) return;
            this.isDeleteConfirmOpen = false;
        },

        async confirmDeleteSelectedNode() {
            await this.deleteSelectedNode();
        },
    },
};
</script>

<style lang="scss" scoped>
.layout-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
    gap: 1rem;
}

.tree-panel,
.context-panel,
.legend-panel {
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.tree-panel,
.context-panel,
.legend-panel {
    padding: 1rem;
}

.side-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.panel-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.panel-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.panel-title,
.context-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
}

.panel-subtitle,
.context-text {
    font-size: 0.9rem;
    color: #6b7280;
}

.tree-search {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 0.85rem;
    padding: 0.7rem 0.85rem;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #f9fafb;
}

.tree-search__icon {
    color: #6b7280;
}

.tree-search__input {
    flex: 1;
    border: 0;
    background: transparent;
    color: #111827;
}

.tree-search__input:focus {
    outline: none;
}

.tree-search__clear {
    border: 0;
    background: transparent;
    color: #6b7280;
    font-size: 0.85rem;
    font-weight: 600;
}

.tree-search__clear:hover {
    color: #111827;
}

.tree-view {
    border: 0;
    padding: 0;
    max-height: 70vh;
    overflow-y: auto;
    padding-right: 0.25rem;
}
/* keep arrow visible but not clickable */
.tree-view :deep(.p-tree-toggler) {
    pointer-events: none;
    cursor: default;
}
.tree-node-button {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    border: 0;
    background: transparent;
    color: #111827;
    padding: 0.15rem 0.35rem;
    border-radius: 8px;
    text-align: left;
    transition:
        background 0.15s ease,
        color 0.15s ease;
}

.tree-node-button:hover {
    background: #f3f4f6;
}

.tree-node-button--active {
    background: #111827;
    color: #ffffff;
}
.tree-node-button__label {
    line-height: 1.35;
}

.tree-node-count {
    display: inline-flex;
    align-items: center;
    border-radius: 9999px;
    padding: 0.1rem 0.5rem;
    font-size: 0.72rem;
    font-weight: 600;
    background: #e5e7eb;
    color: #374151;
    white-space: nowrap;
}

.tree-node-button--active .tree-node-count {
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
}
.context-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 9999px;
    background: #f3f4f6;
    color: #4b5563;
    padding: 0.35rem 0.65rem;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.selected-path {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin: 1rem 0;
    padding: 0.85rem 1rem;
    border-radius: 12px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
}

.selected-path__label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
}
.selected-path__value {
    color: #111827;
    font-weight: 500;
}
.selected-path__meta {
    font-size: 0.82rem;
    font-weight: 600;
    color: #4b5563;
}
.action-card {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    margin: 1rem 0 0;
    padding: 1rem;
    border-radius: 14px;
}

.action-card--warning {
    border: 1px solid #fde68a;
    background: #fffbeb;
}

.action-card--danger {
    border: 1px solid #fecaca;
    background: #fef2f2;
}

.action-card__title {
    font-size: 0.9rem;
    font-weight: 700;
}

.action-card__title--warning {
    color: #92400e;
}

.action-card__title--danger {
    color: #991b1b;
}

.action-card__text {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.5;
}

.action-card__text--warning {
    color: #78350f;
}

.action-card__text--danger {
    color: #7f1d1d;
}
.inline-confirm {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    padding: 0.9rem 1rem;
    border-radius: 12px;
}

.inline-confirm--danger {
    border: 1px solid #fca5a5;
    background: rgba(255, 255, 255, 0.45);
}

.inline-confirm__text {
    font-size: 0.95rem;
    line-height: 1.5;
    color: #7f1d1d;
}

.inline-confirm__actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.form-block {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    margin-top: 1rem;
}

.field-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

.field-row {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.field-icon {
    color: #6b7280;
}

.field-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 0.65rem 0.8rem;
    background: #fff;
}

.field-input:focus {
    outline: none;
    border-color: #9ca3af;
    box-shadow: 0 0 0 3px rgba(156, 163, 175, 0.15);
}

.legend-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.legend-list li {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    color: #374151;
}

.ghost-button {
    border: 1px solid #d1d5db;
    background: #ffffff;
    color: #374151;
    border-radius: 9999px;
    padding: 0.5rem 0.8rem;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.15s ease;
}

.ghost-button:hover {
    background: #f9fafb;
    color: #111827;
}

.empty-state {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-top: 1rem;
    padding: 1rem;
    border-radius: 12px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    color: #4b5563;
}

@media (max-width: 1279px) {
    .layout-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/inertia-vue3";

const actions = [
    {
        key: "books",
        title: "Gestione libri",
        description:
            "Aggiungi posizioni, seleziona un ripiano e inserisci nuovi libri.",
        icon: "pi pi-book",
        tone: "action-card--info",
        cta: "Apri",
        target: "/locations",
    },
    {
        key: "search",
        title: "Consultazione",
        description:
            "Cerca libri già inseriti e controlla rapidamente la loro ubicazione.",
        icon: "pi pi-search",
        tone: "action-card--info",
        cta: "Consulta",
        target: "/books",
    },
    {
        key: "authors",
        title: "Autori",
        description:
            "Esplora gli autori presenti e visualizza i libri associati.",
        icon: "pi pi-user",
        tone: "action-card--info",
        cta: "Apri",
        target: "/authors",
    },
];

function gotoPath(target) {
    window.location.href = `${window.location.origin}${target}`;
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <section class="dashboard-page">
            <div class="dashboard-shell">
                <div class="dashboard-hero">
                    <div class="dashboard-hero-inner">
                        <h1 class="dashboard-title">Dashboard catalogo</h1>
                        <p class="dashboard-subtitle">
                            Gestisci libri, posizioni, autori e manutenzione del
                            catalogo da un unico punto di accesso.
                        </p>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <button
                        v-for="action in actions"
                        :key="action.key"
                        type="button"
                        class="action-card"
                        :class="action.tone"
                        @click="gotoPath(action.target)"
                    >
                        <div class="action-card__top">
                            <div class="action-card__icon-wrap">
                                <i :class="action.icon"></i>
                            </div>
                            <span class="action-card__cta">{{
                                action.cta
                            }}</span>
                        </div>

                        <div class="action-card__body">
                            <h2 class="action-card__title">
                                {{ action.title }}
                            </h2>
                            <p class="action-card__description">
                                {{ action.description }}
                            </p>
                        </div>
                    </button>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
.dashboard-page {
    min-height: 100%;
    padding: 2rem 1rem;
    background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
}

.dashboard-shell {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.dashboard-hero {
    border: 1px solid #e5e7eb;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.92);
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    padding: 2rem;
}

.dashboard-eyebrow {
    margin: 0 0 0.35rem;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #64748b;
}

.dashboard-title {
    margin: 0;
    font-size: clamp(1.9rem, 3vw, 2.6rem);
    line-height: 1.1;
    color: #0f172a;
}

.dashboard-subtitle {
    margin: 0.75rem 0 0;
    max-width: 760px;
    font-size: 1rem;
    line-height: 1.6;
    color: #475569;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
}

.action-card {
    border: 1px solid #e5e7eb;
    border-radius: 22px;
    background: #ffffff;
    padding: 1.1rem;
    text-align: left;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 1.25rem;
    min-height: 220px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    transition:
        transform 0.18s ease,
        box-shadow 0.18s ease,
        border-color 0.18s ease;
    cursor: pointer;
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
}

.action-card:focus-visible {
    outline: 3px solid rgba(59, 130, 246, 0.28);
    outline-offset: 2px;
}

.action-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.action-card__icon-wrap {
    width: 3rem;
    height: 3rem;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    background: #f8fafc;
}

.action-card__cta {
    font-size: 0.85rem;
    font-weight: 700;
    color: #475569;
}

.action-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.action-card__title {
    margin: 0;
    font-size: 1.2rem;
    line-height: 1.25;
    color: #0f172a;
}

.action-card__description {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.55;
    color: #475569;
}

.action-card--primary {
    border-color: #bfdbfe;
    background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
}

.action-card--primary .action-card__icon-wrap {
    background: #dbeafe;
    color: #1d4ed8;
}

.action-card--info {
    border-color: #bae6fd;
    background: linear-gradient(180deg, #ecfeff 0%, #ffffff 100%);
}

.action-card--info .action-card__icon-wrap {
    background: #cffafe;
    color: #0f766e;
}

.action-card--help {
    border-color: #ddd6fe;
    background: linear-gradient(180deg, #f5f3ff 0%, #ffffff 100%);
}

.action-card--help .action-card__icon-wrap {
    background: #ede9fe;
    color: #6d28d9;
}

.action-card--warning {
    border-color: #fde68a;
    background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);
}

.action-card--warning .action-card__icon-wrap {
    background: #fef3c7;
    color: #b45309;
}

.action-card--danger {
    border-color: #fecaca;
    background: linear-gradient(180deg, #fef2f2 0%, #ffffff 100%);
}

.action-card--danger .action-card__icon-wrap {
    background: #fee2e2;
    color: #b91c1c;
}

@media (max-width: 640px) {
    .dashboard-page {
        padding: 1.25rem 0.75rem;
    }

    .dashboard-hero {
        padding: 1.35rem;
    }

    .action-card {
        min-height: 190px;
    }
}
</style>

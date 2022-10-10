<template>
    <section class="container flex flex-col mx-auto mt-4">
        <div class="search w-full columns-1">
            <label for="cerca">Cerca: </label><input id="cerca" v-model="searchTxt" type="text">
            <button @click="search"
                    class="ml-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150">
                Cerca
            </button>

        </div>
        <div class="results w-full columns-1 mt-24">
            <!--            <div class="intestazione flex w-full columns-6 justify-between mt-8">-->
            <!--                <div class="riga w-1/6">ISBN</div>-->
            <!--                <div class="riga w-1/6">TITOLO</div>-->
            <!--                <div class="riga w-1/6">AUTORE</div>-->
            <!--                <div class="riga w-1/6">CASA EDITRICE</div>-->
            <!--                <div class="riga w-1/6">PAGINE</div>-->
            <!--                <div class="riga w-1/6">UBICAZIONE</div>-->
            <!--            </div>-->
            <!--            <div class="risultati flex w-full columns-6 justify-between" v-for="r in results">-->
            <!--                <div class="riga w-1/6">{{r.isbn}}</div>-->
            <!--                <div class="riga w-1/6">{{r.title}}</div>-->
            <!--                <div class="riga w-1/6">-->
            <!--                    <ul>-->
            <!--                        <li v-for="autore in r.authors">{{autore.name}} {{autore.surname}}</li>-->
            <!--                    </ul>-->
            <!--                </div>-->
            <!--                <div class="riga w-1/6">{{r.publisher}}</div>-->
            <!--                <div class="riga w-1/6">{{r.pages}}</div>-->
            <!--                <div class="riga w-1/6">{{r.location.bookshelves.name}} - {{r.location.bookshelves.rooms.name}}</div>-->
            <!--            </div>-->
            <DataTable :value="results" responsiveLayout="scroll">
                <Column field="isbn" header="ISBN" :sortable="true"></Column>
                <Column field="title" header="Titolo" :sortable="true"></Column>
                <Column field="authors" header="Autori" :sortable="true">
                    <template #body="slotProps">
                        <span v-for="c in slotProps.data.authors">{{ c.name }} {{ c.surname }}</span>
                    </template>
                </Column>
                <Column field="publisher" header="Casa editrice" :sortable="true"></Column>
                <Column field="pages" header="Pagine" :sortable="true"></Column>
                <Column field="location" header="Ubicazione" :sortable="true">
                    <template #body="slotProps">

                        <Tree :value="slotProps.data.location">

                        </Tree>

                    </template>
                </Column>
            </DataTable>
        </div>
    </section>
</template>

<script>
export default {
    name: "consultazione",
    data() {
        return {
            searchTxt: null,
            results: null,
            products: null,
            filters1: null,
            nodes: null,
            nodeService: null,
            json:
                [
                    {
                        "key": "0",
                        "label": "Shelf",
                        "data": "Shelf",
                        "icon": "pi pi-fw pi-book",
                        "children": [
                            {
                                "key": "0-0",
                                "label": "Bookshelf",
                                "data": "Bookshelf",
                                "icon": "pi pi-fw pi-server",
                                "children": [
                                    {
                                        "key": "0-0-0",
                                        "label": "Room",
                                        "icon": "pi pi-fw pi-stop",
                                        "data": "Room",
                                        "children": [
                                            {
                                                "key": "0-0-0-0",
                                                "label": "Building",
                                                "icon": "pi pi-fw pi-home",
                                                "data": "Building"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }

                ]
        }
    },


    mounted() {
        this.search()
    },
    methods: {
        parseJson(item) {
            let ss = JSON.stringify(item)
            return JSON.parse(ss)
        },
        search() {
            let path = location.origin + '/search'
            axios.get(path, {
                params: {
                    searchtxt: this.searchTxt
                }
            }).then(r => {
                console.log(r.data)
                this.results = r.data.books
            })
        },
    }

}

</script>

<style scoped>
.risultati, .intestazione {
    border: 1px solid black;
}

.riga {
    border-right: 1px solid black;
    padding: 10px;
}

.risultati:nth-child(even) {
    background-color: #b4b9c0;
}

.p-treenode-leaf {
    display: none;
}
</style>

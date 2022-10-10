<template>
    <section class="inserimento container flex mx-auto mt-4">
        <div class="flex flex-col columns-1 w-1/2">
            <div class="columns-1 mb-4">
                <NavLink :href="route('dashboard')" :active="true"> &lt Torna alla Dashboard</NavLink>
            </div>
            <div class="columns-1 mb-4">
                <NavLink :href="route('ins-pos')" :active="true"> &lt Indietro</NavLink>
            </div>
            <div class="columns-1 mb-4" v-if="breadcrumb">
                <div class="breadcrumb border-solid border-red-600 border-2">
                    <div class="caption">
                        Stai salvando il libro in questa posizione:
                    </div>
                    <div class="path">
                        {{ breadcrumb.bookshelves.rooms.buildings.name }}
                        <font-awesome-icon icon="fa-solid fa-angles-right"/>
                        {{ breadcrumb.bookshelves.rooms.name }}
                        <font-awesome-icon icon="fa-solid fa-angles-right"/>
                        {{ breadcrumb.bookshelves.name }}
                        <font-awesome-icon icon="fa-solid fa-angles-right"/>
                        {{ breadcrumb.name }}
                    </div>
                </div>


            </div>

            <div class="flex columns-2 mb-1.5">
                <div class="w-1/3">
                    <label for="isbn">ISBN</label>
                </div>
                <div class="w-2/3">
                    <input class="ml-auto w-full" type="text" id="isbn" @keyup.enter="checkISBN" v-model="libro.isbn">
                </div>
            </div>

            <div class="flex columns-2 mb-1.5">
                <div class="w-1/3">
                    <label for="titolo">Titolo</label>
                </div>
                <div class="w-2/3">
                    <input class="ml-auto w-full" type="text" id="titolo" v-model="libro.titolo">
                </div>
            </div>
            <div class="flex columns-2 mb-1.5 w-full">
                <div class="w-1/3">
                    <label for="autore">Autore</label>
                </div>
                <div class="w-2/3 flex">
                    <select name="autore" class="w-2/3" id="autore" multiple>
                        <option value="" v-for="aut in autore"> {{aut.nome}} {{aut.cognome}}</option>
                    </select>
                    <div class="w-1/3 flex flex-col ml-1">
                        <input class="" type="text" v-model="insNome" placeholder="Nome">
                        <input class="" type="text" v-model="insCognome" placeholder="Cognome">
                        <button @click="addAuthor" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150">Aggiungi Autore</button>
                    </div>

                </div>


            </div>
            <div class="flex columns-2 mb-1.5">
                <div class="w-1/3">
                    <label for="casaedi">Casa editrice</label>
                </div>
                    <div class="w-2/3">
                        <input class="ml-auto w-full" type="text" id="casaedi" v-model="libro.casaedi">
                    </div>
            </div>
            <div class="flex columns-2 mb-1.5">
                <div class="w-1/3">
                    <label for="pagine">Pagine</label>
                </div>
                    <div class="w-2/3">
                        <input class="ml-auto w-full" type="text" id="pagine" v-model="libro.pagine">
                    </div>
            </div>

            <div class="flex columns-1 mb-1.5">
                <PrimaryButton @click="saveBook">salva</PrimaryButton>
            </div>
        </div>
        <div class="columns-1 w-1/2 pt-28 pl-10">
            <figure v-if="libro.img" class="w-full h-full">
                <img class="" :src="libro.img" alt="img">
            </figure>
        </div>

    </section>
</template>

<script>
import NavLink from '@/Components/NavLink.vue'
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

export default {
    name: "ins-libro",
    components: {PrimaryButton, NavLink, FontAwesomeIcon},
    data() {
        return {
            autore: [],
            libro: {
                isbn: null,
                titolo: null,
                casaedi: null,
                img: null,
                pagine: null,
            },
            idShelf: new URL(window.location.href).pathname.split('/').filter(element => element).pop(),
            breadcrumb: null,
            insNome: null,
            insCognome: null
        }
    },
    mounted() {
        document.getElementById('isbn').focus()
        let url = location.origin + '/get-location'
        axios.get(url, {
            params: {
                idShelf: this.idShelf
            }
        }).then(r => {
            console.log(r.data.data)
            this.breadcrumb = r.data.data
        })

    },
    methods: {
        addAuthor(){
            this.autore.push({
                nome: this.insNome,
                cognome:  this.insCognome
            })

        },
        checkISBN() {
            let path = location.origin
            path = path + '/search'
            axios.get(path, {
                params: {
                    isbn: this.libro.isbn
                }
            }).then(res => {
                if (res.data[0].book) {
                    let book = res.data[0].book
                    this.libro.titolo = book.title_long
                    // this.autore.cognome = book.authors[0].split(', ')[0]
                    // this.autore.nome = book.authors[0].split(', ')[1]
                    book.authors.forEach(item => {
                        let obj = {
                            cognome: item.split(' ')[0].replace(',',''),
                            nome : item.split(' ')[1].replace(',','')
                        }
                        this.autore.push(obj)
                    })
                    this.libro.casaedi = book.publisher
                    this.libro.img = book.image
                    this.libro.pagine = book.pages
                }
            })
        },
        saveBook(){
            let path = location.origin + '/save-book'
            axios.post(path, {
              isbn: this.libro.isbn,
                title: this.libro.titolo,
                publisher: this.libro.casaedi,
                pages: this.libro.pagine,
                shelfId: this.idShelf,
                img: this.libro.img,
                authors: this.autore
            }).then(r => {
                if (r.status == 200){
                    location.reload()
                }
            })
        }
    }
}
</script>

<style scoped>

</style>

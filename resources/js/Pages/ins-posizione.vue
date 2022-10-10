<template>
<section class="ins-posizione container mx-auto mt-4">
    <div class="flex flex-col columns-1 w-1/2">
            <div class="flex columns-1 mb-1.5">
                <label for="casa">Casa</label>
                <select class="ml-auto w-96" name="casa" id="casa" v-model="selBuilding" @change="getRooms">
                    <option v-for="building in buildings" :value="building.id">{{building.name}}</option>
                </select>
            </div>

            <div class="flex columns-1 mb-1.5">
                <label for="stanza">Stanza</label>
                <select class="ml-auto w-96" name="stanza" id="stanza" v-model="selRoom" @change="getBookshelves">
                    <option v-for="room in rooms" :value="room.id">{{room.name}}</option>
                </select>
            </div>
            <div class="flex columns-1 mb-1.5">
                <label for="libreria">Libreria</label>
                <select class="ml-auto w-96" name="libreria" id="libreria" v-model="selBookshelf" @change="getShelves">
                    <option v-for="bookshelf in bookshelves" :value="bookshelf.id">{{bookshelf.name}}</option>
                </select>
            </div>
            <div class="flex columns-1 mb-1.5">
                <label for="ripiano">Ripiano</label>
                <select class="ml-auto w-96" name="ripiano" id="ripiano" v-model="selShelf" @change="showButtonInvia">
                    <option v-for="shelf in shelves" :value="shelf.id">{{shelf.name}}</option>
                </select>
            </div>
        <div class="flex columns-1 mb-1.5">
                <Link v-if="showInvia" :href="'/ins-libro/' + selShelf" as="button" method="get">Avanti</Link>
        </div>
    </div>
</section>
</template>

<script>
import axios from "axios";
import { Link } from '@inertiajs/inertia-vue3'
import PrimaryButton from "@/Components/PrimaryButton.vue";
export default {
    name: "ins-posizione",
    components: {PrimaryButton, Link},
    data() {
        return {
            buildings: [],
            rooms: [],
            bookshelves: [],
            shelves: [],
            selBuilding: null,
            selRoom: null,
            selBookshelf: null,
            selShelf: null,
            showInvia: false,
        }
    },
    mounted() {
        let path = location.origin + '/get-buildings'
        axios.get(path).then(res => {
            this.buildings = res.data
        })
    },
    watch: {
        selShelf () {
            this.showInvia = !!this.selShelf;
        }
    },
    methods: {
        getRooms(){
            let idBuilding = this.selBuilding
            let path = location.origin + '/get-rooms'
            axios.get(path, {
                params: {
                    idBuilding : idBuilding,
                }
            }).then(res => {
                console.log(res.data)
                this.rooms = res.data
                this.selShelf = 0
            })
        },
        getBookshelves(){
            let idRoom= this.selRoom
            let path = location.origin + '/get-bookshelves'
            axios.get(path, {
                params: {
                    idRoom : idRoom,
                }
            }).then(res => {
                console.log(res.data)
                this.bookshelves = res.data
                this.selShelf = 0
            })
        },
        getShelves(){
            let idBookshelf= this.selBookshelf
            let path = location.origin + '/get-shelves'
            axios.get(path, {
                params: {
                    idBookshelf : idBookshelf
                }
            }).then(res => {
                console.log(res.data)
                this.shelves = res.data
                this.selShelf = 0
            })
        },
        showButtonInvia(){
            this.showInvia = !!this.selShelf;
        },
        nextStep(){

        },
    }
}
</script>

<style scoped>

</style>

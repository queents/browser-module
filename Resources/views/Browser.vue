<script setup>
import Header from "@@/Layouts/Includes/Header.vue";
import {createToaster} from "@meforma/vue-toaster";
import {computed, onMounted, ref} from "vue";
import {Inertia} from "@inertiajs/inertia";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import {useTrans} from "@@/Composables/useTrans";
import JetDialogModal from "@@/Jetstream/DialogModal.vue";
import JetSecondaryButton from "@@/Jetstream/SecondaryButton.vue";
import JetButton from "@@/Jetstream/Button.vue";
import ViltForm from "$$/ViltForm.vue";
import JetInput from "@@/Jetstream/Input.vue";
import JetInputError from "@@/Jetstream/InputError.vue";
import JetLabel from "@@/Jetstream/Label.vue";
import "codemirror/lib/codemirror.css";
import "codemirror/mode/php/php.js";
import "codemirror/mode/javascript/javascript.js";
import "codemirror/mode/css/css.js";
import "codemirror/mode/htmlmixed/htmlmixed.js";
import "codemirror/addon/edit/matchbrackets";
import "codemirror/mode/xml/xml.js";
import "codemirror/theme/base16-dark.css";
import "codemirror/mode/clike/clike.js";
import Codemirror from "codemirror-editor-vue3";

const props = defineProps({
    folders: Array,
    files: Array,
    back_path: String,
    back_name: String,
    current_path: String,
    file: Boolean,
    ex: Boolean,
    path: Boolean,
    history: Object,
    rows: Object,
    roles: Object,
    render: Object,
    list: Object,
    data: Object,
});

const toaster = createToaster({ /* options */});

/*
Actions / Modals Data
 */
let actionModal = ref({});
let modalAction = ref({});
let selectedID = ref(null);
let errors = ref({});
let form = ref(useForm({
    file: null,
    path: "",
}));
let uploadModal = ref(false);
let cmOptions = ref({
    tabSize: 4,
    mode: "application/x-httpd-php",
    theme: "base16-dark",
    lineNumbers: true,
    line: true,
    matchBrackets: true,
    indentUnit: 4,
    indentWithTabs: true,
    lineWrapping: true,
});
let imagePath = ref(false)


/*
Methods
 */
function fireAction (name, id = null){
    Inertia.post(route(name), {
        id: id ? id : selectedID.value,
    });
}

function openModal(name, id = null){
    selectedID.value = id;
    actionModal.value[name] = !actionModal.value[name];
}

function modalActionRun(modal, action) {
    if (selectedID.value) {
        modalAction.value[modal].id = selectedID.value;
    }
    let form = useForm(modalAction.value[modal]);
    console.log(form);
    Inertia.post(route(action), form,{
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            actionModal.value[modal] = false;
            success();
        },
    });
}


function openUrl(url){
    window.open(url);
}

/*
Fire Success Response / Error Response
 */

const getMessage = computed({
    get() {
        return props.data.message
    },
    set(value) {
        return value;
    },
});
const success = () => {
    const message = getMessage.value;
    if (typeof message === 'object') {
        if (message.type === 'error') {
            toaster.error(message.message, {
                position: 'top',
            });
        } else if (message.type === 'success') {
            toaster.success(message.message, {
                position: 'top',
            });
        } else {
            toaster.success(message.message, {
                position: 'top',
            });
        }
    } else {
        toaster.success(message, {
            position: 'top',
        });
    }
}

// Load Components
const modals = computed(() => {
    let modalsArray = [];
    if(props.render.components){
        for (let i = 0; i < props.render.components.length; i++) {
            if(props.render.components[i].classType === 'modal'){
                modalsArray.push(props.render.components[i]);
            }
        }
    }

    return modalsArray;
});
const widgets = computed(() => {
    let widgetsArray = [];
    if(props.render.components) {
        for (let i = 0; i < props.render.components.length; i++) {
            if (props.render.components[i].classType === 'widget') {
                widgetsArray.push(props.render.components[i]);
            }
        }
    }
    return widgetsArray;
});
const actions = computed(() => {
    let actionsArray = [];
    if(props.render.components) {
        for (let i = 0; i < props.render.components.length; i++) {
            if (props.render.components[i].classType === 'action') {
                actionsArray.push(props.render.components[i]);
            }
        }
    }
    return actionsArray;
});

// Load Languages
const gLang = computed(
    () => usePage().props.value.data.trans
);
const rLang = computed(
    () => props.render.lang
)

let {trans} = useTrans();

//Load Roles
const roles = props.roles;

// Mounted
onMounted(() => {
    //Set Modals
    if(modals.value.length){
        for (let i = 0; i < modals.value.length; i++) {
            actionModal.value[modals.value[i].name] = false;
            modalAction.value[modals.value[i].name] = useForm({});
        }
    }
})

// Methods
function uploadFile() {
    form.value.path = props.history.current_path;
    Inertia.post(route("browser.upload"), form.value,{
        preserveScroll: false,
        onSuccess: () => {
            form.value.reset();
            uploadModal.value = !uploadModal.value;
            success();
        },
    });
}
function getFolder(data) {
    Inertia.post(route("browser.post"), {
        folder_path: data.path,
        folder_name: data.name,
        type: "folder",
    });
}
function getFile(data) {
    Inertia.post(route("browser.post"), {
        file_path: data.path,
        file_name: data.name,
        type: "file",
    });
}
function goHome() {
    fileContent.value = false;
    imagePath.value = false;
    Inertia.get(route("browser.index"));
}
function goBack() {
    fileContent.value = false;
    imagePath.value = false;
    Inertia.post(route("browser.post"), {
        folder_path: props.history.back_path,
        folder_name: props.history.back_name,
        type: "back",
    });
}
function saveFile() {
    fileContent.value = false;
    imagePath.value = false;
    Inertia.post(
        route("browser.post"),
        {
            path: props.path,
            content: fileContent.value,
            type: "save",
        },
        {
            onSuccess: () => {
                success();
            },
        }
    );
}

// Computed
const fileContent = computed(() => {
    let fileContent = false;
    if (props.ex) {
        if (props.ex == "php") {
            cmOptions.value.mode = "application/x-httpd-php";
            fileContent = props.file;
        } else if (
            props.ex == "js" ||
            props.ex == "json" ||
            props.ex == "lock"
        ) {
            cmOptions.value.mode = {
                name: "javascript",
                json: true,
            };
            fileContent = props.file;
        } else if (props.ex == "css") {
            cmOptions.value.mode = "text/css";
            fileContent = props.file;
        } else if (
            props.ex == "webp" ||
            props.ex == "svg" ||
            props.ex == "png" ||
            props.ex == "jpg" ||
            props.ex == "jpeg" ||
            props.ex == "tif" ||
            props.ex == "gif" ||
            props.ex == "ico"
        ) {
            this.imagePath =
                "data:image/png;base64," + props.file;
        } else {
            fileContent = props.file;
        }
    }
    return fileContent;
});

</script>
<template>
    <div class="px-6 pt-6 mx-auto">
        <!-- Main Resource Header -->
        <Header
            v-if="rLang"
            :canCreate="false"
            :title="rLang ? rLang.index : ''"
            :button="rLang ? rLang.create: ''"
            :url="props.list.url+'.index'"
        >
            <!-- Actions Generator -->
            <a
                v-for="(action, index) in actions"
                :key="index"
                :href="action.url ? action.url : '#'"
                @click.prevent="
                            !action.url
                                ? action.modal
                                    ? openModal(action.modal)
                                    : fireAction(action.action)
                                : openUrl(action.url)
                        "
                class="relative inline-flex items-center px-8 py-3 overflow-hidden text-white bg-main rounded group active:bg-blue-500 focus:outline-none focus:ring"
            >
                        <span
                            class="absolute left-0 transition-transform -translate-x-full group-hover:translate-x-4"
                        >
                            <i class="bx-sm mt-2" :class="action.icon"></i>
                        </span>

                <span
                    class="text-sm font-medium transition-all group-hover:ml-4"
                >
                            {{ action.label }}
                        </span>
            </a>
        </Header>

        <!-- Widgets Generator -->
        <div v-if="widgets.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 my-4">
            <div
                v-for="(item, key) in widgets"
                :class="{
                            'col-span-4 lg:col-span-4 md:col-span-4 sm:col-span-4': widgets.length === 1,
                            'col-span-4 lg:col-span-2 md:col-span-2 sm:col-span-2': widgets.length === 2,
                            'bg-success-500': item.type === 'success',
                            'bg-danger-500': item.type === 'danger',
                            'bg-blue-700': item.type === 'primary',
                            'bg-warning-500': item.type === 'warning',
                        }"
                class="w-full bg-blue-500 rounded-lg text-center py-4 px-2 text-white">

                <i class="bx-lg" :class="item.icon"></i>
                <h1 class="text-2xl font-bold">{{ item.label }}</h1>
                <p>{{ item.value }}</p>
            </div>
        </div>
    </div>

    <div class="px-8 mx-auto">
        <div class="bg-white dark:bg-gray-800 dark:border-gray-600 rounded-lg border">
            <div class="p-8 ">
                <div class="flex justify-start py-4 space-x-2">
                    <button
                        @click="goHome()"
                        class="inline-flex items-center justify-center gap-2 px-4 font-medium tracking-tight text-white transition rounded-lg shadow focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 h-9 focus:ring-white"
                    >
                        <i class="bx bx-home"></i>
                        {{ trans("browser.home") }}
                    </button>
                    <button
                        @click="goBack()"
                        class="inline-flex items-center justify-center gap-2 px-4 font-medium tracking-tight text-white transition rounded-lg shadow focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 h-9 focus:ring-white"
                    >
                        <i class="bx bx-arrow-back"></i>
                        {{ trans("browser.back") }}
                    </button>
                </div>
                <div v-if="fileContent" class="font-sans">
                    <Codemirror
                        v-model:value="fileContent"
                        ref="cmEditor"
                        width="100%"
                        :options="cmOptions"
                        border
                    />
                    <br />
                    <button
                        @click="saveFile()"
                        class="inline-flex items-center justify-center px-4 font-medium tracking-tight text-white transition rounded-lg shadow focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 h-9 focus:ring-white"
                    >
                        <i class="w-6 h-6 p-1 item-center bx bx-user"></i>
                        {{ trans("browser.save") }}
                    </button>
                </div>
                <div v-else-if="imagePath">
                    <img :src="imagePath" />
                </div>
                <div v-else>
                    <div class="grid gap-1 md:grid-cols-6 sm:grid-cols-2">
                        <div v-for="(folder, key) in folders" :key="key">
                            <button
                                @click="getFolder(folder)"
                                class="flex flex-col items-center justify-center w-full p-4 font-medium text-center border rounded"
                            >
                                <i
                                    class="item-center text-primary-500 bx bxs-folder bx-lg"
                                ></i>
                                {{ folder.name.substring(0, 20) }}
                            </button>
                        </div>
                        <div v-for="(file, key) in files" :key="key">
                            <button
                                @click="getFile(file)"
                                class="flex flex-col items-center justify-center w-full p-4 font-medium text-center border rounded"
                            >
                                <i
                                    v-if="
                                            file.ex == 'png' ||
                                            file.ex == 'jpg' ||
                                            file.ex == 'jpeg' ||
                                            file.ex == 'svg' ||
                                            file.ex == 'webp'
                                        "
                                    class="item-center text-primary-500 bx bxs-image bx-lg"
                                ></i>

                                <i
                                    v-else
                                    class="item-center bx bx-lg"
                                    :class="[
                                            file.ex == 'md'
                                                ? 'bxs-file-md'
                                                : 'bxs-file',
                                            file.ex == 'js'
                                                ? 'bxs-file-js'
                                                : 'bxs-file',
                                            file.ex == 'json'
                                                ? 'bxs-file-json'
                                                : 'bxs-file',
                                        ]"
                                ></i>
                                {{ file.name.substring(0, 20) }}
                            </button>
                        </div>
                    </div>
                    <div v-if="!folders.length && !files.length">
                        <div class="px-4 py-4">
                            <i
                                class="mx-auto my-2 bx bx-search bx-lg item-center text-primary-500"
                            ></i>
                            <h1 class="text-3xl font-bold text-center">
                                Sorry No Folders or Files!
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
            <JetDialogModal
                :show="uploadModal"
                @close="uploadModal = !uploadModal"
            >
                <template #title>
                    Please Select The File And Upload It
                    <hr class="my-4" />
                </template>

                <template #content>
                    <div class="py-2">
                        <ViltMedia
                            id="file"
                            name="file"
                            v-model="form.file"
                            :preview="false"
                            :message="form.errors.file ? form.errors.file : ''"
                            :multi="true"
                        />
                    </div>
                </template>

                <template #footer>
                    <JetButton @click.prevent="uploadFile" class="mx-2"
                    >Upload</JetButton
                    >
                    <JetSecondaryButton @click="uploadModal = !uploadModal">
                        Close
                    </JetSecondaryButton>
                </template>
            </JetDialogModal>
        </div>
    </div>

    <!-- Modals Generator -->
    <JetDialogModal
        v-for="(item, key) in modals"
        :key="key"
        :show="actionModal[item.name]"
    >
        <template #title>
            <div class="flex justify-between">
                {{ item.label }}
            </div>
        </template>

        <template #content>
            <form action="" enctype="multipart/form-data">
                <ViltForm
                    v-model="modalAction[item.name]"
                    :rows="item.rows"
                    :errors="modalAction[item.name].errors"
                />
            </form>
        </template>

        <template #footer>
            <JetButton
                v-for="(button, key) in item.buttons"
                :key="key"
                @click.prevent="modalActionRun(item.name, button.action)"
                class="mx-2"
            >{{ button.label }}
            </JetButton>
            <JetSecondaryButton
                @click="actionModal[item.name] = !actionModal[item.name]"
            >
                {{ trans('global.close') }}
            </JetSecondaryButton>
        </template>
    </JetDialogModal>
</template>

<script>
import { defineComponent } from "vue";
import AppLayout from "@@/Layouts/AppLayout.vue";

export default defineComponent({
    layout: AppLayout,
});
</script>

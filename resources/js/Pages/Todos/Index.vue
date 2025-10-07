<template>
    <Head title="My Todos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Todo List</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Add New Todo Form -->
                        <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium mb-4">Add New Todo</h3>
                            <form @submit.prevent="submitTodo" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="title" value="Title" />
                                        <TextInput
                                            id="title"
                                            v-model="form.title"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                            placeholder="Enter todo title..."
                                        />
                                        <InputError :message="form.errors.title" class="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel for="priority" value="Priority" />
                                        <select
                                            id="priority"
                                            v-model="form.priority"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                        </select>
                                        <InputError :message="form.errors.priority" class="mt-2" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="description" value="Description" />
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            rows="3"
                                            placeholder="Enter description (optional)..."
                                        ></textarea>
                                        <InputError :message="form.errors.description" class="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel for="due_date" value="Due Date" />
                                        <TextInput
                                            id="due_date"
                                            v-model="form.due_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.due_date" class="mt-2" />
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <PrimaryButton :disabled="form.processing">
                                        Add Todo
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>

                        <!-- Filter Controls -->
                        <div class="mb-6 flex flex-wrap gap-4">
                            <div>
                                <select
                                    v-model="filterStatus"
                                    @change="updateFilters"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">All Todos</option>
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div>
                                <select
                                    v-model="filterPriority"
                                    @change="updateFilters"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">All Priorities</option>
                                    <option value="low">Low Priority</option>
                                    <option value="medium">Medium Priority</option>
                                    <option value="high">High Priority</option>
                                </select>
                            </div>
                        </div>

                        <!-- Todo Stats -->
                        <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ totalTodos }}</div>
                                <div class="text-blue-800">Total Todos</div>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-yellow-600">{{ pendingTodos }}</div>
                                <div class="text-yellow-800">Pending</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-green-600">{{ completedTodos }}</div>
                                <div class="text-green-800">Completed</div>
                            </div>
                        </div>

                        <!-- Todos List -->
                        <div class="space-y-4">
                            <div v-if="todos.length === 0" class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="mt-2">No todos found. Create your first todo above!</p>
                            </div>

                            <div
                                v-for="todo in todos"
                                :key="todo.id"
                                class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow"
                                :class="{
                                    'opacity-60': todo.completed,
                                    'border-l-4 border-l-red-500': todo.priority === 'high',
                                    'border-l-4 border-l-yellow-500': todo.priority === 'medium',
                                    'border-l-4 border-l-green-500': todo.priority === 'low'
                                }"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-3 flex-1">
                                        <button
                                            @click="toggleTodo(todo)"
                                            class="mt-1 flex-shrink-0"
                                        >
                                            <svg
                                                v-if="todo.completed"
                                                class="h-5 w-5 text-green-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <svg
                                                v-else
                                                class="h-5 w-5 text-gray-400 hover:text-green-500"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                        <div class="flex-1">
                                            <h4
                                                class="font-medium"
                                                :class="{ 'line-through text-gray-500': todo.completed }"
                                            >
                                                {{ todo.title }}
                                            </h4>
                                            <p
                                                v-if="todo.description"
                                                class="text-gray-600 text-sm mt-1"
                                                :class="{ 'line-through': todo.completed }"
                                            >
                                                {{ todo.description }}
                                            </p>
                                            <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                                <span class="capitalize px-2 py-1 rounded-full text-xs"
                                                    :class="{
                                                        'bg-red-100 text-red-800': todo.priority === 'high',
                                                        'bg-yellow-100 text-yellow-800': todo.priority === 'medium',
                                                        'bg-green-100 text-green-800': todo.priority === 'low'
                                                    }"
                                                >
                                                    {{ todo.priority }} priority
                                                </span>
                                                <span v-if="todo.due_date">
                                                    Due: {{ formatDate(todo.due_date) }}
                                                </span>
                                                <span>
                                                    Created: {{ formatDate(todo.created_at) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button
                                            @click="startEditing(todo)"
                                            class="text-blue-600 hover:text-blue-800"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="deleteTodo(todo)"
                                            class="text-red-600 hover:text-red-800"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Todo Modal -->
        <Modal :show="editingTodo !== null" @close="cancelEditing">
            <div class="p-6">
                <h3 class="text-lg font-medium mb-4">Edit Todo</h3>
                <form @submit.prevent="updateTodo" class="space-y-4">
                    <div>
                        <InputLabel for="edit_title" value="Title" />
                        <TextInput
                            id="edit_title"
                            v-model="editForm.title"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="editForm.errors.title" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="edit_description" value="Description" />
                        <textarea
                            id="edit_description"
                            v-model="editForm.description"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            rows="3"
                        ></textarea>
                        <InputError :message="editForm.errors.description" class="mt-2" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="edit_priority" value="Priority" />
                            <select
                                id="edit_priority"
                                v-model="editForm.priority"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="edit_due_date" value="Due Date" />
                            <TextInput
                                id="edit_due_date"
                                v-model="editForm.due_date"
                                type="date"
                                class="mt-1 block w-full"
                            />
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <SecondaryButton @click="cancelEditing">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="editForm.processing">
                            Update Todo
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    todos: Array,
    filters: Object
})

// Form for creating new todos
const form = useForm({
    title: '',
    description: '',
    priority: 'medium',
    due_date: ''
})

// Form for editing todos
const editForm = useForm({
    title: '',
    description: '',
    priority: 'medium',
    due_date: ''
})

// Editing state
const editingTodo = ref(null)

// Filter states
const filterStatus = ref(props.filters?.filter || '')
const filterPriority = ref(props.filters?.priority || '')

// Computed properties for stats
const totalTodos = computed(() => props.todos.length)
const pendingTodos = computed(() => props.todos.filter(todo => !todo.completed).length)
const completedTodos = computed(() => props.todos.filter(todo => todo.completed).length)

// Methods
const submitTodo = () => {
    form.post(route('todos.store'), {
        onSuccess: () => {
            form.reset()
        }
    })
}

const toggleTodo = (todo) => {
    router.patch(route('todos.toggle', todo.id), {}, {
        preserveScroll: true
    })
}

const startEditing = (todo) => {
    editingTodo.value = todo
    editForm.title = todo.title
    editForm.description = todo.description || ''
    editForm.priority = todo.priority
    editForm.due_date = todo.due_date || ''
}

const cancelEditing = () => {
    editingTodo.value = null
    editForm.reset()
    editForm.clearErrors()
}

const updateTodo = () => {
    editForm.patch(route('todos.update', editingTodo.value.id), {
        onSuccess: () => {
            cancelEditing()
        }
    })
}

const deleteTodo = (todo) => {
    if (confirm('Are you sure you want to delete this todo?')) {
        router.delete(route('todos.destroy', todo.id), {
            preserveScroll: true
        })
    }
}

const updateFilters = () => {
    router.get(route('todos.index'), {
        filter: filterStatus.value,
        priority: filterPriority.value
    }, {
        preserveState: true,
        replace: true
    })
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>
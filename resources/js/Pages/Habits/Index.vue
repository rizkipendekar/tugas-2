<template>
    <Head title="Habits" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Habits Tracker</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Create Habit Form -->
                        <div class="mb-8 bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-medium mb-4">Create New Habit</h3>
                            <form @submit.prevent="submitForm" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="name" value="Habit Name" />
                                        <TextInput
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            placeholder="e.g., Drink 8 glasses of water"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.name" />
                                    </div>
                                    <div>
                                        <InputLabel for="frequency" value="Frequency" />
                                        <select
                                            id="frequency"
                                            v-model="form.frequency"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            required
                                        >
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.frequency" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="target_count" value="Target Count" />
                                        <TextInput
                                            id="target_count"
                                            v-model="form.target_count"
                                            type="number"
                                            class="mt-1 block w-full"
                                            min="1"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.target_count" />
                                    </div>
                                    <div>
                                        <InputLabel for="color" value="Color" />
                                        <input
                                            id="color"
                                            v-model="form.color"
                                            type="color"
                                            class="mt-1 block w-16 h-10 border-gray-300 rounded-md"
                                        />
                                        <InputError class="mt-2" :message="form.errors.color" />
                                    </div>
                                </div>
                                <div>
                                    <InputLabel for="description" value="Description (optional)" />
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        rows="3"
                                        placeholder="Optional description or notes about this habit"
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>
                                <div class="flex justify-end">
                                    <PrimaryButton :disabled="form.processing">
                                        Create Habit
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>

                        <!-- Filter Buttons -->
                        <div class="mb-6 flex space-x-2">
                            <button
                                @click="changeFilter('all')"
                                :class="[
                                    'px-4 py-2 rounded-lg font-medium transition-colors',
                                    filters.filter === 'all' || !filters.filter
                                        ? 'bg-blue-500 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                ]"
                            >
                                All Habits
                            </button>
                            <button
                                @click="changeFilter('active')"
                                :class="[
                                    'px-4 py-2 rounded-lg font-medium transition-colors',
                                    filters.filter === 'active'
                                        ? 'bg-green-500 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                ]"
                            >
                                Active
                            </button>
                            <button
                                @click="changeFilter('inactive')"
                                :class="[
                                    'px-4 py-2 rounded-lg font-medium transition-colors',
                                    filters.filter === 'inactive'
                                        ? 'bg-red-500 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                ]"
                            >
                                Inactive
                            </button>
                        </div>

                        <!-- Statistics Overview -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                            <div class="bg-blue-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ totalHabits }}</div>
                                <div class="text-blue-800">Total Habits</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-green-600">{{ activeHabits }}</div>
                                <div class="text-green-800">Active</div>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-yellow-600">{{ completedToday }}</div>
                                <div class="text-yellow-800">Completed Today</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ averageStreak }}</div>
                                <div class="text-purple-800">Avg Streak</div>
                            </div>
                        </div>

                        <!-- Habits List -->
                        <div class="space-y-4">
                            <div v-if="habits.length === 0" class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-2">No habits found. Create your first habit above!</p>
                            </div>

                            <div
                                v-for="habit in habits"
                                :key="habit.id"
                                class="border rounded-lg p-6 hover:shadow-md transition-shadow"
                                :style="{ borderLeftColor: habit.color, borderLeftWidth: '4px' }"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h4 class="font-medium text-lg">{{ habit.name }}</h4>
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 text-green-800': habit.is_active,
                                                    'bg-red-100 text-red-800': !habit.is_active
                                                }"
                                            >
                                                {{ habit.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800 capitalize">
                                                {{ habit.frequency }}
                                            </span>
                                        </div>
                                        <p v-if="habit.description" class="text-gray-600 text-sm mb-3">
                                            {{ habit.description }}
                                        </p>
                                        
                                        <!-- Progress Stats -->
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Current Streak:</span>
                                                <span class="font-medium ml-1">{{ habit.current_streak }} days</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Weekly:</span>
                                                <span class="font-medium ml-1">{{ habit.weekly_completion }}%</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Monthly:</span>
                                                <span class="font-medium ml-1">{{ habit.monthly_completion }}%</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Target:</span>
                                                <span class="font-medium ml-1">{{ habit.target_count }} times</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex flex-col space-y-2 ml-4">
                                        <button
                                            v-if="!habit.completed_today"
                                            @click="completeHabit(habit.id)"
                                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm"
                                        >
                                            Mark Complete
                                        </button>
                                        <button
                                            v-else
                                            @click="uncompleteHabit(habit.id)"
                                            class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors text-sm"
                                        >
                                            Undo ({{ habit.today_entry?.count || 0 }})
                                        </button>
                                        <div class="flex space-x-1">
                                            <button
                                                @click="startEditing(habit)"
                                                class="px-3 py-1 text-blue-600 hover:text-blue-800 border border-blue-300 rounded text-sm"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="deleteHabit(habit.id)"
                                                class="px-3 py-1 text-red-600 hover:text-red-800 border border-red-300 rounded text-sm"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Modal -->
                        <Modal :show="editingHabit !== null" @close="cancelEditing">
                            <div class="p-6">
                                <h3 class="text-lg font-medium mb-4">Edit Habit</h3>
                                <form @submit.prevent="updateHabit" class="space-y-4">
                                    <div>
                                        <InputLabel for="edit_name" value="Habit Name" />
                                        <TextInput
                                            id="edit_name"
                                            v-model="editForm.name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="edit_frequency" value="Frequency" />
                                            <select
                                                id="edit_frequency"
                                                v-model="editForm.frequency"
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                required
                                            >
                                                <option value="daily">Daily</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="monthly">Monthly</option>
                                            </select>
                                        </div>
                                        <div>
                                            <InputLabel for="edit_target_count" value="Target Count" />
                                            <TextInput
                                                id="edit_target_count"
                                                v-model="editForm.target_count"
                                                type="number"
                                                class="mt-1 block w-full"
                                                required
                                            />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="edit_color" value="Color" />
                                            <input
                                                id="edit_color"
                                                v-model="editForm.color"
                                                type="color"
                                                class="mt-1 block w-16 h-10 border-gray-300 rounded-md"
                                            />
                                        </div>
                                        <div class="flex items-center">
                                            <input
                                                id="edit_is_active"
                                                v-model="editForm.is_active"
                                                type="checkbox"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            />
                                            <InputLabel for="edit_is_active" value="Active" class="ml-2" />
                                        </div>
                                    </div>
                                    <div>
                                        <InputLabel for="edit_description" value="Description" />
                                        <textarea
                                            id="edit_description"
                                            v-model="editForm.description"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            rows="3"
                                        ></textarea>
                                    </div>
                                    <div class="flex justify-end space-x-3">
                                        <SecondaryButton @click="cancelEditing">Cancel</SecondaryButton>
                                        <PrimaryButton :disabled="editForm.processing">
                                            Update Habit
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </Modal>
                    </div>
                </div>
            </div>
        </div>
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
    habits: Array,
    filters: Object
})

// Form for creating new habits
const form = useForm({
    name: '',
    description: '',
    frequency: 'daily',
    target_count: 1,
    color: '#3B82F6'
})

// Form for editing habits
const editForm = useForm({
    name: '',
    description: '',
    frequency: 'daily',
    target_count: 1,
    color: '#3B82F6',
    is_active: true
})

const editingHabit = ref(null)

// Computed statistics
const totalHabits = computed(() => props.habits.length)
const activeHabits = computed(() => props.habits.filter(habit => habit.is_active).length)
const completedToday = computed(() => props.habits.filter(habit => habit.completed_today).length)
const averageStreak = computed(() => {
    if (props.habits.length === 0) return 0
    const totalStreak = props.habits.reduce((sum, habit) => sum + habit.current_streak, 0)
    return Math.round(totalStreak / props.habits.length)
})

// Methods
const submitForm = () => {
    form.post(route('habits.store'), {
        onSuccess: () => {
            form.reset()
        }
    })
}

const completeHabit = (habitId) => {
    router.post(route('habits.complete', habitId))
}

const uncompleteHabit = (habitId) => {
    router.delete(route('habits.uncomplete', habitId))
}

const startEditing = (habit) => {
    editingHabit.value = habit
    editForm.name = habit.name
    editForm.description = habit.description || ''
    editForm.frequency = habit.frequency
    editForm.target_count = habit.target_count
    editForm.color = habit.color
    editForm.is_active = habit.is_active
}

const cancelEditing = () => {
    editingHabit.value = null
    editForm.reset()
}

const updateHabit = () => {
    editForm.patch(route('habits.update', editingHabit.value.id), {
        onSuccess: () => {
            cancelEditing()
        }
    })
}

const deleteHabit = (habitId) => {
    if (confirm('Are you sure you want to delete this habit? This action cannot be undone.')) {
        router.delete(route('habits.destroy', habitId))
    }
}

const changeFilter = (filter) => {
    router.get(route('habits.index'), { filter: filter === 'all' ? null : filter })
}
</script>
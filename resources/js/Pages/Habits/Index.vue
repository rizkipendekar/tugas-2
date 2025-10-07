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

                        <!-- Error Message -->
                        <div v-if="error" class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Database Error</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p>{{ error }}</p>
                                        <p class="mt-2">Please run: <code class="bg-red-200 px-2 py-1 rounded">C:\laragon\bin\php\php-8.2.29-Win32-vs16-x86\php.exe run-migrations.php</code></p>
                                    </div>
                                </div>
                            </div>
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
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
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
                                        
                                        <!-- Today's Comment Preview -->
                                        <div v-if="habit.today_entry?.notes" class="bg-blue-50 p-2 rounded text-xs text-blue-800 mb-2">
                                            <span class="font-medium">Today's note:</span> {{ habit.today_entry.notes.substring(0, 100) }}{{ habit.today_entry.notes.length > 100 ? '...' : '' }}
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex flex-col space-y-2 ml-4">
                                        <button
                                            v-if="!habit.completed_today"
                                            @click="openCompleteModal(habit)"
                                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm"
                                        >
                                            Mark Complete
                                        </button>
                                        <button
                                            v-else
                                            @click="viewHabitNotes(habit)"
                                            class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors text-sm"
                                        >
                                            Completed ({{ habit.today_entry?.count || 0 }})
                                        </button>
                                        <div class="flex space-x-1">
                                            <button
                                                @click="startEditing(habit)"
                                                class="px-3 py-1 text-blue-600 hover:text-blue-800 border border-blue-300 rounded text-sm"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="viewHabitHistory(habit)"
                                                class="px-3 py-1 text-purple-600 hover:text-purple-800 border border-purple-300 rounded text-sm"
                                            >
                                                History
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

                        <!-- Complete Habit Modal -->
                        <Modal :show="completingHabit !== null" @close="cancelCompleting">
                            <div class="p-6">
                                <h3 class="text-lg font-medium mb-4">Complete Habit: {{ completingHabit?.name }}</h3>
                                <form @submit.prevent="submitComplete" class="space-y-4">
                                    <div>
                                        <InputLabel for="complete_count" value="How many times?" />
                                        <TextInput
                                            id="complete_count"
                                            v-model="completeForm.count"
                                            type="number"
                                            class="mt-1 block w-full"
                                            min="1"
                                            required
                                        />
                                        <p class="text-sm text-gray-600 mt-1">Target: {{ completingHabit?.target_count }} times</p>
                                    </div>
                                    <div>
                                        <InputLabel for="complete_notes" value="Add a comment (optional)" />
                                        <textarea
                                            id="complete_notes"
                                            v-model="completeForm.notes"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            rows="3"
                                            placeholder="How did it go? Any thoughts or reflections..."
                                        ></textarea>
                                    </div>
                                    <div class="flex justify-end space-x-3">
                                        <SecondaryButton @click="cancelCompleting">Cancel</SecondaryButton>
                                        <PrimaryButton :disabled="completeForm.processing">
                                            Mark Complete
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </Modal>

                        <!-- View Notes Modal -->
                        <Modal :show="viewingNotes !== null" @close="cancelViewingNotes">
                            <div class="p-6">
                                <h3 class="text-lg font-medium mb-4">{{ viewingNotes?.name }} - Today's Entry</h3>
                                <div class="space-y-4">
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span class="font-medium text-gray-700">Completed:</span>
                                                <span class="ml-2">{{ viewingNotes?.today_entry?.count || 0 }} times</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-700">Target:</span>
                                                <span class="ml-2">{{ viewingNotes?.target_count }} times</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div v-if="viewingNotes?.today_entry?.notes">
                                        <h4 class="font-medium text-gray-700 mb-2">Today's Comment:</h4>
                                        <div class="bg-blue-50 p-3 rounded-lg text-sm text-gray-700">
                                            {{ viewingNotes.today_entry.notes }}
                                        </div>
                                    </div>
                                    
                                    <div v-else class="text-gray-500 text-sm text-center py-4">
                                        No comment added for today
                                    </div>
                                    
                                    <div class="flex justify-end space-x-3">
                                        <SecondaryButton @click="uncompleteHabit(viewingNotes.id)">Undo Complete</SecondaryButton>
                                        <SecondaryButton @click="cancelViewingNotes">Close</SecondaryButton>
                                    </div>
                                </div>
                            </div>
                        </Modal>

                        <!-- Habit History Modal -->
                        <Modal :show="viewingHistory !== null" @close="cancelViewingHistory" maxWidth="4xl">
                            <div class="p-6">
                                <h3 class="text-lg font-medium mb-4">{{ viewingHistory?.name }} - History</h3>
                                <div class="space-y-4 max-h-96 overflow-y-auto">
                                    <div v-if="habitHistory.length === 0" class="text-center py-8 text-gray-500">
                                        <p>No history entries found for this habit</p>
                                    </div>
                                    
                                    <div v-for="entry in habitHistory" :key="entry.id" class="border rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span class="font-medium text-gray-900">{{ formatHistoryDate(entry.completed_at) }}</span>
                                                <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                                    {{ entry.count }} time{{ entry.count > 1 ? 's' : '' }}
                                                </span>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ getRelativeDate(entry.completed_at) }}</span>
                                        </div>
                                        
                                        <div v-if="entry.notes" class="bg-gray-50 p-3 rounded text-sm text-gray-700">
                                            <span class="font-medium">Comment:</span> {{ entry.notes }}
                                        </div>
                                        <div v-else class="text-xs text-gray-400 italic">
                                            No comment added
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end mt-6">
                                    <SecondaryButton @click="cancelViewingHistory">Close</SecondaryButton>
                                </div>
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
    filters: Object,
    error: String
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
const completingHabit = ref(null)
const viewingNotes = ref(null)
const viewingHistory = ref(null)
const habitHistory = ref([])

// Form for completing habits with comments
const completeForm = useForm({
    count: 1,
    notes: ''
})

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
    console.log('Completing habit:', habitId)
    router.post(route('habits.complete', habitId), {}, {
        onSuccess: () => {
            console.log('Habit completed successfully')
        },
        onError: (errors) => {
            console.error('Error completing habit:', errors)
        }
    })
}

const openCompleteModal = (habit) => {
    completingHabit.value = habit
    completeForm.count = 1
    completeForm.notes = ''
}

const cancelCompleting = () => {
    completingHabit.value = null
    completeForm.reset()
}

const submitComplete = () => {
    const habitId = completingHabit.value.id
    completeForm.post(route('habits.complete', habitId), {
        onSuccess: () => {
            console.log('Habit completed successfully with comment')
            cancelCompleting()
        },
        onError: (errors) => {
            console.error('Error completing habit:', errors)
        }
    })
}

const viewHabitNotes = (habit) => {
    viewingNotes.value = habit
}

const cancelViewingNotes = () => {
    viewingNotes.value = null
}

const viewHabitHistory = async (habit) => {
    try {
        viewingHistory.value = habit
        // Fetch habit history via API
        const response = await fetch(route('habits.statistics', habit.id))
        const data = await response.json()
        habitHistory.value = data.entries || []
    } catch (error) {
        console.error('Error fetching habit history:', error)
        habitHistory.value = []
    }
}

const cancelViewingHistory = () => {
    viewingHistory.value = null
    habitHistory.value = []
}

const formatHistoryDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    })
}

const getRelativeDate = (dateString) => {
    const date = new Date(dateString)
    const today = new Date()
    const diffTime = today - date
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24))
    
    if (diffDays === 0) return 'Today'
    if (diffDays === 1) return 'Yesterday'
    if (diffDays < 7) return `${diffDays} days ago`
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`
    return `${Math.floor(diffDays / 30)} months ago`
}

const uncompleteHabit = (habitId) => {
    console.log('Uncompleting habit:', habitId)
    router.delete(route('habits.uncomplete', habitId), {
        onSuccess: () => {
            console.log('Habit uncompleted successfully')
        },
        onError: (errors) => {
            console.error('Error uncompleting habit:', errors)
        }
    })
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
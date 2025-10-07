<template>
    <Head title="Calendar & Goals" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Calendar & Goals</h2>
                <div class="flex items-center space-x-4">
                    <!-- User Progress Display -->
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-4 py-2 rounded-lg">
                        <div class="flex items-center space-x-2">
                            <div class="text-lg font-bold">Level {{ userProgress.level }}</div>
                            <div class="text-sm">{{ userProgress.total_points }} pts</div>
                            <div class="text-sm">🔥 {{ userProgress.streak_days }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Gamification Dashboard -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <!-- XP Progress -->
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-blue-800">Experience</span>
                                    <span class="text-xs text-blue-600">{{ userProgress.xp_to_next_level }} XP to next level</span>
                                </div>
                                <div class="w-full bg-blue-200 rounded-full h-2 mb-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: userProgress.xp_progress + '%' }"></div>
                                </div>
                                <div class="text-lg font-bold text-blue-900">{{ userProgress.experience }} XP</div>
                            </div>

                            <!-- Daily Points -->
                            <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg">
                                <div class="text-sm font-medium text-green-800 mb-2">Today's Points</div>
                                <div class="text-2xl font-bold text-green-900">{{ userProgress.daily_points }}</div>
                                <div class="text-xs text-green-600">Keep going! 🎯</div>
                            </div>

                            <!-- Streak -->
                            <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-lg">
                                <div class="text-sm font-medium text-orange-800 mb-2">Current Streak</div>
                                <div class="text-2xl font-bold text-orange-900">{{ userProgress.streak_days }}</div>
                                <div class="text-xs text-orange-600">days in a row 🔥</div>
                            </div>

                            <!-- Total Points -->
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg">
                                <div class="text-sm font-medium text-purple-800 mb-2">Total Points</div>
                                <div class="text-2xl font-bold text-purple-900">{{ userProgress.total_points }}</div>
                                <div class="text-xs text-purple-600">lifetime earned ⭐</div>
                            </div>
                        </div>

                        <!-- Active Goals -->
                        <div v-if="activeGoals.length > 0" class="mb-6">
                            <h3 class="text-lg font-semibold mb-3">Active Goals 🎯</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="goal in activeGoals" :key="goal.id" class="border rounded-lg p-4 bg-gradient-to-r from-yellow-50 to-yellow-100">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ goal.title }}</h4>
                                            <p class="text-sm text-gray-600">{{ goal.description }}</p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-gray-500">{{ goal.days_remaining }} days left</div>
                                            <div class="text-sm font-medium">{{ goal.current_progress }}/{{ goal.target_points }} pts</div>
                                        </div>
                                    </div>
                                    <div class="w-full bg-yellow-200 rounded-full h-2 mb-2">
                                        <div class="bg-yellow-500 h-2 rounded-full transition-all duration-300" :style="{ width: goal.progress_percentage + '%' }"></div>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-600">{{ goal.progress_percentage }}% complete</span>
                                        <span class="text-yellow-700">{{ goal.reward_icon }} {{ goal.reward_name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Interface -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Calendar Header -->
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center space-x-4">
                                <h3 class="text-xl font-semibold">{{ formatMonth(currentDate) }}</h3>
                                <div class="flex space-x-2">
                                    <button @click="changeMonth(-1)" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">←</button>
                                    <button @click="changeMonth(1)" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">→</button>
                                    <button @click="goToToday" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Today</button>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <button 
                                    @click="openTaskModal"
                                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600"
                                >
                                    Add Task
                                </button>
                                <button 
                                    @click="openGoalModal"
                                    class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600"
                                >
                                    Add Goal
                                </button>
                            </div>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7 gap-1 mb-4">
                            <!-- Week headers -->
                            <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" 
                                 :key="day" 
                                 class="p-2 text-center font-medium text-gray-500 text-sm">
                                {{ day }}
                            </div>
                            
                            <!-- Calendar days -->
                            <div v-for="date in calendarDays" 
                                 :key="date.dateString"
                                 class="min-h-[100px] border border-gray-200 p-1 cursor-pointer hover:bg-gray-50"
                                 :class="{
                                     'bg-blue-50': date.isToday,
                                     'text-gray-400': !date.isCurrentMonth,
                                     'bg-green-50': date.hasCompletedTasks,
                                 }"
                                 @click="selectDate(date.dateString)">
                                
                                <div class="text-sm font-medium mb-1" 
                                     :class="{ 'text-blue-600': date.isToday }">
                                    {{ date.day }}
                                </div>
                                
                                <!-- Tasks for this date -->
                                <div class="space-y-1">
                                    <div v-for="task in getTasksForDate(date.dateString).slice(0, 3)" 
                                         :key="task.id"
                                         class="text-xs p-1 rounded truncate"
                                         :class="{
                                             'bg-green-200 text-green-800': task.completed,
                                             'bg-red-200 text-red-800': task.priority === 'high' && !task.completed,
                                             'bg-yellow-200 text-yellow-800': task.priority === 'medium' && !task.completed,
                                             'bg-blue-200 text-blue-800': task.priority === 'low' && !task.completed,
                                         }"
                                         @click.stop="viewTask(task)">
                                        {{ task.title }} ({{ task.points }}pts)
                                    </div>
                                    
                                    <div v-if="getTasksForDate(date.dateString).length > 3" 
                                         class="text-xs text-gray-500">
                                        +{{ getTasksForDate(date.dateString).length - 3 }} more
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Modal -->
                <Modal :show="showTaskModal" @close="closeTaskModal">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">{{ editingTask ? 'Edit Task' : 'Add New Task' }}</h3>
                        <form @submit.prevent="saveTask" class="space-y-4">
                            <div>
                                <InputLabel for="task_title" value="Task Title" />
                                <TextInput
                                    id="task_title"
                                    v-model="taskForm.title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                            </div>
                            
                            <div>
                                <InputLabel for="task_description" value="Description" />
                                <textarea
                                    id="task_description"
                                    v-model="taskForm.description"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    rows="3"
                                ></textarea>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="task_date" value="Date" />
                                    <TextInput
                                        id="task_date"
                                        v-model="taskForm.task_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                </div>
                                
                                <div>
                                    <InputLabel for="task_time" value="Time (optional)" />
                                    <TextInput
                                        id="task_time"
                                        v-model="taskForm.task_time"
                                        type="time"
                                        class="mt-1 block w-full"
                                    />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <InputLabel for="task_points" value="Points" />
                                    <TextInput
                                        id="task_points"
                                        v-model="taskForm.points"
                                        type="number"
                                        class="mt-1 block w-full"
                                        min="1"
                                        max="100"
                                        required
                                    />
                                </div>
                                
                                <div>
                                    <InputLabel for="task_priority" value="Priority" />
                                    <select
                                        id="task_priority"
                                        v-model="taskForm.priority"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <InputLabel for="task_category" value="Category" />
                                    <select
                                        id="task_category"
                                        v-model="taskForm.category"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Select...</option>
                                        <option v-for="category in categories" :key="category" :value="category">
                                            {{ category.charAt(0).toUpperCase() + category.slice(1) }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <SecondaryButton @click="closeTaskModal">Cancel</SecondaryButton>
                                <PrimaryButton :disabled="taskForm.processing">
                                    {{ editingTask ? 'Update' : 'Create' }} Task
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </Modal>

                <!-- Goal Modal -->
                <Modal :show="showGoalModal" @close="closeGoalModal">
                    <div class="p-6">
                        <h3 class="text-lg font-medium mb-4">Create New Goal</h3>
                        <form @submit.prevent="saveGoal" class="space-y-4">
                            <div>
                                <InputLabel for="goal_title" value="Goal Title" />
                                <TextInput
                                    id="goal_title"
                                    v-model="goalForm.title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                            </div>
                            
                            <div>
                                <InputLabel for="goal_description" value="Description" />
                                <textarea
                                    id="goal_description"
                                    v-model="goalForm.description"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    rows="3"
                                ></textarea>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <InputLabel for="goal_target_points" value="Target Points" />
                                    <TextInput
                                        id="goal_target_points"
                                        v-model="goalForm.target_points"
                                        type="number"
                                        class="mt-1 block w-full"
                                        min="1"
                                        required
                                    />
                                </div>
                                
                                <div>
                                    <InputLabel for="goal_start_date" value="Start Date" />
                                    <TextInput
                                        id="goal_start_date"
                                        v-model="goalForm.start_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                </div>
                                
                                <div>
                                    <InputLabel for="goal_end_date" value="End Date" />
                                    <TextInput
                                        id="goal_end_date"
                                        v-model="goalForm.end_date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <InputLabel for="goal_reward_name" value="Reward Name" />
                                    <TextInput
                                        id="goal_reward_name"
                                        v-model="goalForm.reward_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="e.g., Champion Badge"
                                    />
                                </div>
                                
                                <div>
                                    <InputLabel for="goal_reward_icon" value="Reward Icon" />
                                    <TextInput
                                        id="goal_reward_icon"
                                        v-model="goalForm.reward_icon"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="🏆"
                                    />
                                </div>
                                
                                <div>
                                    <InputLabel for="goal_reward_color" value="Reward Color" />
                                    <input
                                        id="goal_reward_color"
                                        v-model="goalForm.reward_color"
                                        type="color"
                                        class="mt-1 block w-16 h-10 border-gray-300 rounded-md"
                                    />
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <SecondaryButton @click="closeGoalModal">Cancel</SecondaryButton>
                                <PrimaryButton :disabled="goalForm.processing">
                                    Create Goal
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </Modal>

                <!-- Task View Modal -->
                <Modal :show="viewingTask !== null" @close="closeTaskView">
                    <div class="p-6" v-if="viewingTask">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-medium">{{ viewingTask.title }}</h3>
                                <p class="text-gray-600">{{ viewingTask.description }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-green-600">{{ viewingTask.points }} pts</div>
                                <div class="text-sm text-gray-500">{{ viewingTask.priority }} priority</div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                            <div>
                                <span class="font-medium">Date:</span> {{ formatDate(viewingTask.task_date) }}
                            </div>
                            <div v-if="viewingTask.task_time">
                                <span class="font-medium">Time:</span> {{ viewingTask.formatted_time }}
                            </div>
                            <div v-if="viewingTask.category">
                                <span class="font-medium">Category:</span> {{ viewingTask.category }}
                            </div>
                            <div>
                                <span class="font-medium">Status:</span> 
                                <span :class="viewingTask.completed ? 'text-green-600' : 'text-yellow-600'">
                                    {{ viewingTask.completed ? 'Completed' : 'Pending' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <SecondaryButton @click="editTask(viewingTask)">Edit</SecondaryButton>
                            <SecondaryButton 
                                @click="toggleTaskComplete(viewingTask)"
                                :class="viewingTask.completed ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600'"
                                class="text-white"
                            >
                                {{ viewingTask.completed ? 'Mark Incomplete' : 'Mark Complete' }}
                            </SecondaryButton>
                            <button
                                @click="deleteTask(viewingTask)"
                                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </Modal>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    tasks: Object,
    currentDate: String,
    viewMode: String,
    userProgress: Object,
    activeGoals: Array,
    recentAchievements: Array,
    categories: Array
})

// Reactive variables
const showTaskModal = ref(false)
const showGoalModal = ref(false)
const editingTask = ref(null)
const viewingTask = ref(null)
const selectedDate = ref(props.currentDate)

// Forms
const taskForm = useForm({
    title: '',
    description: '',
    task_date: props.currentDate,
    task_time: '',
    points: 10,
    priority: 'medium',
    category: ''
})

const goalForm = useForm({
    title: '',
    description: '',
    target_points: 100,
    start_date: new Date().toISOString().split('T')[0],
    end_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    reward_name: '',
    reward_icon: '🏆',
    reward_color: '#FFD700'
})

// Calendar computation
const calendarDays = computed(() => {
    const currentDateObj = new Date(selectedDate.value)
    const year = currentDateObj.getFullYear()
    const month = currentDateObj.getMonth()
    const today = new Date()
    
    const firstDay = new Date(year, month, 1)
    const lastDay = new Date(year, month + 1, 0)
    const startDate = new Date(firstDay)
    startDate.setDate(startDate.getDate() - firstDay.getDay())
    
    const days = []
    const currentDay = new Date(startDate)
    
    for (let i = 0; i < 42; i++) {
        const dateString = currentDay.toISOString().split('T')[0]
        const tasksForDay = getTasksForDate(dateString)
        
        days.push({
            day: currentDay.getDate(),
            dateString: dateString,
            isCurrentMonth: currentDay.getMonth() === month,
            isToday: dateString === today.toISOString().split('T')[0],
            hasCompletedTasks: tasksForDay.some(task => task.completed)
        })
        
        currentDay.setDate(currentDay.getDate() + 1)
    }
    
    return days
})

// Methods
const getTasksForDate = (date) => {
    return props.tasks[date] || []
}

const formatMonth = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    })
}

const changeMonth = (direction) => {
    const currentDateObj = new Date(selectedDate.value)
    currentDateObj.setMonth(currentDateObj.getMonth() + direction)
    selectedDate.value = currentDateObj.toISOString().split('T')[0]
    
    router.get(route('calendar.index'), {
        date: selectedDate.value,
        view: props.viewMode
    })
}

const goToToday = () => {
    const today = new Date().toISOString().split('T')[0]
    selectedDate.value = today
    
    router.get(route('calendar.index'), {
        date: today,
        view: props.viewMode
    })
}

const selectDate = (date) => {
    taskForm.task_date = date
    openTaskModal()
}

const openTaskModal = () => {
    editingTask.value = null
    taskForm.reset()
    taskForm.task_date = selectedDate.value
    taskForm.points = 10
    taskForm.priority = 'medium'
    showTaskModal.value = true
}

const closeTaskModal = () => {
    showTaskModal.value = false
    editingTask.value = null
    taskForm.reset()
}

const saveTask = () => {
    if (editingTask.value) {
        taskForm.patch(route('calendar.tasks.update', editingTask.value.id), {
            onSuccess: () => {
                closeTaskModal()
            }
        })
    } else {
        taskForm.post(route('calendar.tasks.store'), {
            onSuccess: () => {
                closeTaskModal()
            }
        })
    }
}

const editTask = (task) => {
    editingTask.value = task
    taskForm.title = task.title
    taskForm.description = task.description || ''
    taskForm.task_date = task.task_date
    taskForm.task_time = task.formatted_time || ''
    taskForm.points = task.points
    taskForm.priority = task.priority
    taskForm.category = task.category || ''
    showTaskModal.value = true
    closeTaskView()
}

const viewTask = (task) => {
    viewingTask.value = task
}

const closeTaskView = () => {
    viewingTask.value = null
}

const toggleTaskComplete = (task) => {
    router.patch(route('calendar.tasks.toggle', task.id), {}, {
        onSuccess: () => {
            closeTaskView()
        }
    })
}

const deleteTask = (task) => {
    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(route('calendar.tasks.destroy', task.id), {
            onSuccess: () => {
                closeTaskView()
            }
        })
    }
}

const openGoalModal = () => {
    goalForm.reset()
    goalForm.start_date = new Date().toISOString().split('T')[0]
    goalForm.end_date = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
    goalForm.target_points = 100
    goalForm.reward_icon = '🏆'
    goalForm.reward_color = '#FFD700'
    showGoalModal.value = true
}

const closeGoalModal = () => {
    showGoalModal.value = false
    goalForm.reset()
}

const saveGoal = () => {
    goalForm.post(route('calendar.goals.store'), {
        onSuccess: () => {
            closeGoalModal()
        }
    })
}

onMounted(() => {
    // Set initial selected date
    selectedDate.value = props.currentDate
})
</script>
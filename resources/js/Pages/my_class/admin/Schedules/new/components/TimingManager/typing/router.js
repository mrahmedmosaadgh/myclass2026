import { createRouter, createWebHistory } from 'vue-router'

const routes = [
    {
        path: '/typing',
        component: () => import('./views/TypingHome.vue'),
        children: [
            {
                path: 'lesson/urk',
                name: 'lesson.urk',
                component: () => import('./views/URKLesson.vue')
            }
        ]
    }
]

export default createRouter({
    history: createWebHistory(),
    routes
})
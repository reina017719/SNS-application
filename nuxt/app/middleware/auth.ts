export default defineNuxtRouteMiddleware((to, from) => {
    const { isLoggedIn } = useAuth()

    // 未ログインならログイン画面へ
    if (!isLoggedIn.value) {
        return navigateTo('/login')
    }
})
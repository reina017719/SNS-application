export const useAuth = () => {
    const token = useState<string | null>('token', () => null)

    if (import.meta.client && token.value === null) {
        token.value = localStorage.getItem('token')
    }

    const isLoggedIn = computed(() => !!token.value)

    const setToken = (newToken: string) => {
        token.value = newToken
        if (import.meta.client) {
            localStorage.setItem('token', newToken)
        }
    }

    const logout = async () => {
        try {
            await $fetch('/auth/logout', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${token.value}`,
                    Accept: 'application/json',
                },
            })
        } catch (e) {
            console.warn('logout api failed')
        }

        // フロント側 token 削除
        token.value = null
        if (import.meta.client) {
            localStorage.removeItem('token')
        }

        navigateTo('/login')
    }

    return {
        token,
        isLoggedIn,
        setToken,
        logout,
    }
}
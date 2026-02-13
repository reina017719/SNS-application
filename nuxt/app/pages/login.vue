<template>
    <div class="login-page">
    <!-- ヘッダー -->
        <header class="header">
            <img class="logo" src="/icons/logo.png" alt="shareロゴ" />
            <nav class="nav">
                <NuxtLink class="nav-link" to="/register">新規登録</NuxtLink>
                <NuxtLink class="nav-link" to="/login">ログイン</NuxtLink>
            </nav>
        </header>
        <!-- ログインカード -->
        <div class="login-card">
            <h2 class="title">ログイン</h2>
            <Form @submit="login">
                <div class="form-group">
                    <Field name="email" rules="required|email" v-slot="{ field }">
                        <input v-bind="field" class="input" type="email" placeholder="メールアドレス" />
                    </Field>
                    <ErrorMessage name="email" class="error" />
                </div>
                <div class="form-group">
                    <Field name="password" rules="required|min:6" v-slot="{ field }">
                        <input v-bind="field" class="input" type="password" placeholder="パスワード" />
                    </Field>
                    <ErrorMessage name="password" class="error" />
                </div>
                <button class="login-button" type="submit">
                    ログイン
                </button>
            </Form>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from '#app'
import { Form, Field, ErrorMessage } from 'vee-validate'

const router = useRouter()
const { setToken } = useAuth()

const config = useRuntimeConfig()

/* ログイン */
const login = async (values) => {
    try {
        const res = await $fetch('/auth/login', {
            baseURL: config.public.apiBase,
            method: 'POST',
            body: {
                email: values.email,
                password: values.password,
            },
        })

        // JWT保存
        setToken(res.token)

        /* ホーム(index)へ遷移 */
        router.push('/')
    } catch (error) {
        alert('メールアドレスまたはパスワードが違います')
        console.error(error)
    }
}
</script>

<style scoped>
:global(body) {
    margin: 0;
}

/* 全体 */
.login-page {
    min-height: 100vh;
    background: linear-gradient(180deg, #0f1c2b, #0b1725);
    color: #fff;
    position: relative;
}

/* ヘッダー */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 40px;
}

.logo {
    width: 150px;
}

.nav {
    display: flex;
    gap: 24px;
}

.nav-link {
    color: #fff;
    text-decoration: none;
    font-size: 14px;
}

/* ログインカード */
.login-card {
    position: absolute;
    top: 45%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    color: #000;
    width: 380px;
    padding: 5px 32px 32px;
    border-radius: 8px;
    text-align: center;
}

.title {
    margin-bottom: 20px;
    font-size: 18px;
}

.form-group {
    width: 100%;
    margin-bottom: 16px;
}

/* 入力欄 */
.input {
    width: 90%;
    padding: 12px 14px;
    margin-bottom: 4px;
    border-radius: 10px;
    border: 1px solid #333;
    font-size: 14px;
}

.login-button {
    margin-top: 8px;
    padding: 10px 32px;
    border-radius: 20px;
    border: px;
    background: #4a2ada;
    color: #fff;
    cursor: pointer;
    font-size: 14px;
}

.login-button:hover {
    opacity: 0.9;
}

.error {
    color: #e63946;
    font-size: 12px;
    margin: 0;
    text-align: left;
    white-space: nowrap;
}
</style>

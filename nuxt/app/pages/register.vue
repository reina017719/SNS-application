<template>
    <div class="register-page">
        <!-- ヘッダー -->
        <header class="header">
            <img class="logo" src="/icons/logo.png" alt="shareロゴ" />
            <nav class="nav">
                <NuxtLink class="nav-link" to="/register">新規登録</NuxtLink>
                <NuxtLink class="nav-link" to="/login">ログイン</NuxtLink>
            </nav>
        </header>
        <!-- 登録カード -->
        <div class="register-card">
            <h2 class="title">新規登録</h2>
            <Form @submit="register">
                <div class="form-group">
                    <Field name="username" rules="required|max:20" v-slot="{ field }">
                        <input v-bind="field" class="input" type="text" placeholder="ユーザーネーム" />
                    </Field>
                    <ErrorMessage name="username" class="error" />
                </div>
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
                <button class="register-button" type="submit">
                    新規登録
                </button>
            </Form>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from '#app'
import { Form, Field, ErrorMessage } from 'vee-validate'

const router = useRouter()
const config = useRuntimeConfig()
const { setToken } = useAuth()

/* 新規登録 */
const register = async (values) => {
    try {
        const response = await $fetch(
            `${config.public.apiBase}/auth/register`,
            {
                method: 'POST',
                body: {
                    username: values.username,
                    email: values.email,
                    password: values.password,
                },
                headers: {
                    Accept: 'application/json',
                },
            }
        )
        // JWT保存
        setToken(response.token)

        // ホームへ
        router.push('/')
    } catch (error) {
        console.error('登録エラー', error)
        alert('登録に失敗しました')
    }
}
</script>

<style scoped>
:global(body) {
    margin: 0;
}

/* 全体 */
.register-page {
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

/* 登録カード */
.register-card {
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

.register-button {
    margin-top: 8px;
    padding: 10px 32px;
    border-radius: 20px;
    border: px;
    background: #4a2ada;
    color: #fff;
    cursor: pointer;
    font-size: 14px;
}

.register-button:hover {
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

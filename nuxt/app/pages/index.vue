<template>
    <div class="layout">
    <!-- サイドバー -->
        <aside class="sidebar">
            <img class="logo" src="/icons/logo.png" alt="shareロゴ">
            <nav class="menu">
                <div class="menu-item">
                    <NuxtLink to="/">
                        <span>
                            <img class="home-icon" src="/icons/home.png" alt="homeロゴ">
                        </span>
                        <button class="sidebar-button" type="button">
                            <span>ホーム</span>
                        </button>
                    </NuxtLink>
                </div>
                <div class="menu-item">
                    <span>
                        <img class="logout-icon" src="/icons/logout.png" alt="ログアウトロゴ">
                    </span>
                    <button class="sidebar-button" type="button" @click="logout">
                        <span>ログアウト</span>
                    </button>
                </div>
                <!-- シェア投稿 -->
                <div class="share-box">
                    <p class="share-title">シェア</p>
                    <Form @submit="sharePost">
                        <div class="form-group">
                            <Field name="message" rules="required|max:120" v-slot="{ field }">
                                <textarea v-bind="field" placeholder=""></textarea>
                            </Field>
                            <ErrorMessage name="message" class="error" />
                        </div>
                        <button class="share-button" type="submit">
                            シェアする
                        </button>
                    </Form>
                </div>
            </nav>
        </aside>

        <!-- メイン -->
        <main class="main">
            <header class="header">
                <h3 class="home">ホーム</h3>
            </header>
            <section class="post" v-for="post in posts" :key="post.id">
                <div class="post-header">
                    <span class="username">{{ post.user.name }}</span>
                    <button class="icon-button" type="button" @click="toggleLike(post)">
                        <img class="favorite-icon" src="/icons/heart.png" alt="いいねアイコン">
                        <span class="like" v-if="post.likes_count > 0">{{ post.likes_count }}</span>
                    </button>
                    <span class="delete">
                        <button class="icon-button" type="button" @click="deletePost(post.id)">
                            <img class="delete-icon" src="/icons/cross.png" alt="削除アイコン">
                        </button>
                    </span>
                    <span class="share">
                        <button class="icon-button" type="button" @click="goDetail(post.id)">
                            <img class="detail-icon" src="/icons/detail.png" alt="矢印ロゴ">
                        </button>
                    </span>
                </div>
                <p class="message">{{ post.message }}</p>
            </section>
        </main>
    </div>
</template>

<script setup>
import { useRouter } from '#app'
import { Form, Field, ErrorMessage } from 'vee-validate'
import { onMounted, ref } from 'vue'

const router = useRouter()
const { token, isLoggedIn, logout } = useAuth()
const config = useRuntimeConfig()
const posts = ref([])

/* 投稿一覧取得 */
const fetchPosts = async () => {
    try {
        posts.value = await $fetch(`${config.public.apiBase}/posts`, {
            headers: {
                Authorization: `Bearer ${token.value}`,
                Accept: 'application/json',
            },
        })
        console.log('取得した投稿:', posts.value)
    } catch (error) {
        console.error('投稿取得エラー', error)
    }
}

/* 投稿機能 */
const sharePost = async (values, {resetForm }) => {
    try {
        await $fetch(`${config.public.apiBase}/posts`, {
            method: 'POST',
            body: {
                message: values.message,
            },
            headers: {
                Authorization: `Bearer ${token.value}`,
                Accept: 'application/json',
            },
        })
        // 投稿後に一覧を再取得
        await fetchPosts()
        // フォーム初期化
        resetForm()
    } catch (error) {
        console.error('投稿エラー', error)
        alert('投稿に失敗しました')
    }
}

/* いいね機能 */
const toggleLike = async (post) => {
    try {
        if (post.is_liked) {
            // いいね解除
            await $fetch(`${config.public.apiBase}/posts/${post.id}/likes`, {
                method: 'DELETE',
                headers: {
                    Authorization: `Bearer ${token.value}`,
                    Accept: 'application/json',
                },
            })

            post.is_liked = false
            post.likes_count =Math.max(0, post.likes_count - 1)
        } else {
            // いいね
            await $fetch(`${config.public.apiBase}/posts/${post.id}/likes`, {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${token.value}`,
                    Accept: 'application/json',
                },
            })

            post.is_liked = true
            post.likes_count++
        }
        await fetchPosts()
    } catch (error) {
        console.error('いいねエラー', error)
        alert('いいね処理に失敗しました')
    }
}

/* 削除機能 */
const deletePost = async (id) => {
    if (!confirm('この投稿を削除しますか？')) return

    try {
        await $fetch(`${config.public.apiBase}/posts/${id}`, {
            method: 'DELETE',
            headers: {
                Authorization: `Bearer ${token.value}`,
                Accept: 'application/json',
            },
        })

        //成功したら一覧を再取得
        await fetchPosts()
    } catch (error) {
        if (error?.response?.status === 403) {
            alert('自分の投稿のみ削除できます')
        } else {
            console.error('削除エラー', error)
            alert('削除に失敗しました')
        }
    }
}

/* 詳細画面への遷移 */
const goDetail = (id) => {
    router.push(`/posts/${id}`)
}

onMounted(() => {
    console.log('ログイン状態:', isLoggedIn.value)
    fetchPosts()
})

/* 未ログイン時にloginへ遷移 */
definePageMeta({
    middleware: 'auth'
})
</script>

<style scoped>
:global(body) {
    margin: 0;
}

.layout {
    display: flex;
    min-height: 100vh;
    background: #0b1725;
    color: #fff;
}

/* ===== サイドバー ===== */
.sidebar {
    width: 260px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.logo {
    width: 100px;
}

.menu {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.menu-item {
    display: flex;
    gap: 8px;
    align-items: center;
    cursor: pointer;
}

.home-icon {
    width: 25px;
}

.logout-icon {
    width: 25px;
}

.sidebar-button {
    font-size: 15px;
    border: none;
    background: #0b1725;
    color: #fff;
    cursor: pointer;
}

.share-box {
    margin-top: auto;
}

.share-title {
    margin-bottom: 8px;
}

textarea {
    width: 100%;
    height: 100px;
    background: transparent;
    border: 1px solid #ccc;
    border-radius: 8px;
    color: #fff;
    padding: 8px;
    resize: none;
}

.share-button {
    margin-top: 12px;
    margin-left: auto;
    display: block;
    width: 40%;
    padding: 10px;
    border-radius: 20px;
    border: none;
    background: #6c4cff;
    box-shadow: -1px -1px 5px #fff;
    color: #fff;
    cursor: pointer;
}

/* ===== メイン ===== */
.main {
    flex: 1;
    padding: 10px;
}

.header {
    padding-bottom: 10px;
}

.home {
    margin-left: 15px;
}

.post {
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 16px;
}

.post-header {
    display: flex;
    align-items: center;
    gap: 12px;
}

.favorite-icon {
    width: 20px;
}

.like {
    color: #fff;
}

.delete-icon {
    width: 20px;
}

.detail-icon {
    width: 20px;
    padding-left: 30px;
}

.icon-button {
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
}

.icon-button img {
    display: block;
}

.icon-button:focus {
    outline: none;
}

.message {
    margin-top: 12px;
}

.form-group {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.error {
    color: #e63946;
    font-size: 15px;
    margin-top: 4px;
    text-align: left;
}
</style>

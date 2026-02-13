<template>
    <div class="layout">
    <!-- サイドバー -->
        <aside class="sidebar">
            <img class="logo" src="/icons/logo.png" alt="shareロゴ" />
            <nav class="menu">
                <div class="menu-item">
                    <NuxtLink to="/">
                        <span>
                            <img class="home-icon" src="/icons/home.png" alt="homeロゴ" />
                        </span>
                        <button class="sidebar-button" type="button">
                            <span>ホーム</span>
                        </button>
                    </NuxtLink>
                </div>
                <div class="menu-item">
                    <span>
                        <img class="logout-icon" src="/icons/logout.png" alt="ログアウトロゴ" />
                    </span>
                    <button class="sidebar-button" type="button" @click="logout">
                        <span>ログアウト</span>
                    </button>
                </div>
                <div class="share-box">
                    <p class="share-title">シェア</p>
                    <Form @submit="sharePostAndBack">
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
                <h3 class="comment-header">コメント</h3>
            </header>
            <div v-if="post" class="post">
                <div class="post-header">
                    <span class="username">{{ post?.user?.name }}</span>
                    <button v-if="post" class="icon-button" type="button" @click="toggleLike(post)">
                        <img class="favorite-icon" src="/icons/heart.png" alt="いいねアイコン">
                        <span class="like" v-if="post?.likes_count > 0">{{ post?.likes_count }}</span>
                    </button>
                    <span class="delete">
                        <button class="icon-button" type="button" @click="deletePostAndBack(post.id)">
                            <img class="delete-icon" src="/icons/cross.png" alt="削除アイコン" />
                        </button>
                    </span>
                </div>
                <p class="message">{{ post?.message }}</p>
            </div>
            <h4>コメント</h4>
            <div class="post" v-for="comment in comments" :key="comment.id">
                <div class="post-header">
                    <span class="username">{{ comment.user?.name }}</span>
                </div>
                <p class="message">{{ comment.message }}</p>
            </div>
            <div class="comment">
                <Form @submit="addComment">
                    <div class="form-group">
                        <Field name="comment" rules="required|max:120" v-slot="{ field }">
                            <textarea v-bind="field" class="comment-area" placeholder=""></textarea>
                        </Field>
                        <ErrorMessage name="comment" class="error"/>
                    </div>
                    <button class="comment-button" type="submit">
                        コメント
                    </button>
                </Form>
            </div>
        </main>
    </div>
</template>

<script setup>
import { useRouter, useRoute } from '#app'
import { Form, Field, ErrorMessage } from 'vee-validate'

const router = useRouter()
const route = useRoute()
const postId = Number(route.params.id)
const post = ref(null)

/* ログアウト機能 */
const { logout } = useAuth()

const config = useRuntimeConfig()
const { token } = useAuth()

const fetchPost = async () => {
    post.value = await $fetch(
        `${config.public.apiBase}/posts/${postId}`,
        {
            headers: {
                Authorization: `Bearer ${token.value}`,
                Accept: 'application/json',
            },
        }
    )
}

onMounted(() => {
    fetchPost()
})

/*　投稿(シェア)してホームへ */
const sharePostAndBack = async (values, {resetForm}) => {
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
        resetForm()
        router.push('/')
    } catch (error) {
        console.error('投稿エラー', error)
        alert('投稿に失敗しました')
    }
}

const comments = ref([])

/* 対象投稿のコメントだけ取得 */
const fetchComments = async () => {
    try {
        comments.value = await $fetch(
            `${config.public.apiBase}/posts/${postId}/comments`,
            {
                headers: {
                    Authorization: `Bearer ${token.value}`,
                    Accept: 'application/json',
                },
            }
        )
    } catch (error) {
        console.error('コメント取得エラー', error)
    }
}

onMounted(() => {
    fetchComments()
})

/* コメント投稿 */
const addComment = async (values, { resetForm }) => {
    try {
        const newComment = await $fetch(
            `${config.public.apiBase}/posts/${postId}/comments`,
            {
                method: 'POST',
                body: {
                    message: values.comment,
                },
                headers: {
                    Authorization: `Bearer ${token.value}`,
                    Accept: 'application/json',
                },
            }
        )
        await fetchComments()
        resetForm()
    } catch (error) {
        console.error('コメント投稿エラー', error)
        alert('コメントの投稿に失敗しました')
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
        await fetchPost()
    } catch (error) {
        console.error('いいねエラー', error)
        alert('いいね処理に失敗しました')
    }
}

/* 削除機能 */
const deletePostAndBack = async (id) => {
    if (!confirm('この投稿を削除しますか？')) return

    try {
        await $fetch(`${config.public.apiBase}/posts/${id}`, {
            method: 'DELETE',
            headers: {
                Authorization: `Bearer ${token.value}`,
                Accept: 'application/json',
            },
        })
        router.push('/')
    } catch (error) {
        console.error('削除エラー', error)
        alert('削除に失敗しました')
    }
}

// 未ログイン時にloginへ遷移
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

.comment-header {
    margin-left: 15px;
}

.post {
    margin-bottom: 5px;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 10px;
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

h4 {
    text-align: center;
}

.comment-area {
    width: 100%;
    height: 50px;
    margin-top: 20px;
    box-sizing: border-box;
    background: transparent;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 10px;
    resize: none;
}

.comment-button {
    margin-top: 8px;
    margin-left: auto;
    display: block;
    width: 110px;
    padding: 10px;
    border-radius: 20px;
    border: none;
    background: #6c4cff;
    box-shadow: -1px -1px 5px #fff;
    color: #fff;
    cursor: pointer;
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
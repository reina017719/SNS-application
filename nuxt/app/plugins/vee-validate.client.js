import { defineNuxtPlugin } from '#app'
import { defineRule, configure } from 'vee-validate'
import { required, email, min, max } from '@vee-validate/rules'

export default defineNuxtPlugin((nuxtApp) => {
    /* ルール登録 */
    defineRule('required', required)
    defineRule('email', email)
    defineRule('min', min)
    defineRule('max', max)

    /* エラーメッセージ設定(日本語) */
    configure({
        generateMessage: (ctx) => {
            const messages = {
                required: '必須項目です',
                email: '正しいメールアドレスを入力してください',
                min: `${ctx.rule.params[0]}文字以上で入力してください`,
                max: `${ctx.rule.params[0]}文字以内で入力してください`,
            }
            return messages[ctx.rule.name] || '入力内容が正しくありません'
        }
    })
})

<template>
    <header>
        <div class="content">
            <router-link to="/" class="logo">
                <img src="../assets/icons/logo.svg" alt="logo">
                <span>管理サイト</span>
            </router-link>
            <nav v-if="isAdminLoggedIn">
                <router-link to="/admin/catalog"><span>商品管理</span></router-link>
                <router-link to="/admin/clients"><span>顧客管理</span></router-link>
                <router-link to="/admin/orders"><span>注文管理</span></router-link>
                <div class="account">
                    <img src="../assets/icons/user.svg" alt="logo">
                    <span class="name">{{ admin_user['name'] }}</span>
                    <button @click="admin_logout" class="button danger"><span>ログアウト</span></button>
                </div>
            </nav>
        </div>
    </header>
</template>
<script>

import { defineComponent, inject } from 'vue';

export default defineComponent({
    name: 'AdminMainHeader',
    setup() {

        const { admin_user, isAdminLoggedIn, admin_logout } = inject('admin_auth')

        console.log(admin_user);



        return {
            admin_user,
            isAdminLoggedIn,
            admin_logout
        };
    }
});
</script>

<style lang="scss" scoped>
header {
    border-bottom: 1px solid #d9d9d9;
    padding: 0px 32px 0px 32px;
    height: 90px;
    background: #fff;

    display: flex;
    align-items: center;

    .content {
        display: flex;
        width: -webkit-fill-available;
        gap: 30px;

        .logo {
            display: flex;
            gap: 24px;


            font-weight: 400;
            font-size: 16px;
            color: #000;
            align-items: center;
            text-decoration: unset;

            img {
                width: 34px;
            }

            span {
                width: max-content !important;
            }
        }


        nav {
            display: flex;
            justify-content: start;
            gap: 8px;
            align-items: center;
            width: -webkit-fill-available;

            a {
                text-decoration: none;
                font-weight: 400;
                font-size: 16px;
                line-height: 100%;
                color: #1e1e1e;
                padding: 0px 8px 0px 8px;
                align-items: center;
                display: flex;
                height: 32px;

                &:hover,
                &.router-link-exact-active {
                    background: #f5f5f5;
                    border-radius: 8px;
                }


            }

            button {
                padding: 8px;
                height: 32px;
            }

            .account {
                align-items: center;
                display: flex;
                gap: 10px;
                margin-left: auto;
                margin-right: 0px;

                .name {
                    font-weight: 400;
                    font-size: 16px;
                    line-height: 100%;
                    color: #000;
                }

                img {
                    width: 24px;
                }
            }
        }
    }
}
</style>

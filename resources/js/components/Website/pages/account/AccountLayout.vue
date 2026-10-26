<template>
    <section class="ks-user-account" :class="pageLang === 'ar' ? 'ks-user-account--ar' : 'ks-user-account--en'">
        <div class="ks-container">
            <div class="ks-user-account-shell">
                <aside class="ks-user-account-aside">
                    <div class="ks-user-account-user">
                        <div class="ks-user-account-avatar-wrap">
                            <img
                                :src="avatarUrl"
                                :alt="user?.name || 'User'"
                                class="ks-user-account-avatar"
                            />
                        </div>
                        <div class="ks-user-account-user-meta">
                            <strong>{{ user?.name || "—" }}</strong>
                            <span>{{ user?.email || "" }}</span>
                        </div>
                    </div>

                    <nav class="ks-user-account-nav" aria-label="Account">
                        <router-link
                            v-for="link in navLinks"
                            :key="link.to"
                            :to="link.to"
                            class="ks-user-account-nav-link"
                            :class="{ 'is-active': isActive(link.to) }"
                        >
                            <span class="ks-user-account-nav-ico" aria-hidden="true">{{ link.icon }}</span>
                            <span>{{ link.label }}</span>
                        </router-link>
                        <button type="button" class="ks-user-account-nav-link ks-user-account-nav-link--logout" @click="logout">
                            <span class="ks-user-account-nav-ico" aria-hidden="true">⎋</span>
                            <span>{{ t.nav.logout }}</span>
                        </button>
                    </nav>
                </aside>

                <main class="ks-user-account-main">
                    <router-view />
                </main>
            </div>
        </div>
    </section>
</template>

<script>
import { mapGetters } from "vuex";
import { authApi } from "@/axiosUser";
import { accountCopy } from "@/constants/accountI18n.js";

const DEFAULT_AVATAR = "/assets/media/svg/avatars/blank.svg";

export default {
    name: "AccountLayout",
    computed: {
        ...mapGetters(["getUser"]),
        locale() {
            return this.$route.params.locale || "en";
        },
        pageLang() {
            return this.locale === "ar" ? "ar" : "en";
        },
        t() {
            return accountCopy(this.pageLang);
        },
        user() {
            return this.getUser;
        },
        avatarUrl() {
            return this.user?.logo || DEFAULT_AVATAR;
        },
        navLinks() {
            const base = `/${this.locale}/account`;
            return [
                { to: base, label: this.t.nav.dashboard, icon: "⌂", exact: true },
                { to: `${base}/profile`, label: this.t.nav.profile, icon: "👤" },
                { to: `${base}/orders`, label: this.t.nav.orders, icon: "📦" },
                { to: `/${this.locale}/cart`, label: this.t.nav.cart, icon: "🛒", external: true },
            ];
        },
    },
    mounted() {
        const token = localStorage.getItem("authToken") || localStorage.getItem("token");
        if (!token) {
            this.$router.replace(`/${this.locale}/login`);
            return;
        }
        if (!this.user) {
            try {
                const cached = localStorage.getItem("user");
                if (cached) {
                    this.$store.commit("setUser", JSON.parse(cached));
                }
            } catch (_) {
                /* ignore invalid cache */
            }
        }
        if (!this.user) {
            this.$store.dispatch("fetchUser");
        }
    },
    methods: {
        isActive(path) {
            if (path === `/${this.locale}/account`) {
                return this.$route.path === path || this.$route.path === `${path}/`;
            }
            return this.$route.path.startsWith(path);
        },
        async logout() {
            try {
                await authApi.post("auth/logOut");
            } catch (_) {
                /* still clear local session */
            }
            localStorage.removeItem("authToken");
            localStorage.removeItem("token");
            localStorage.removeItem("user");
            this.$store.commit("setUser", null);
            this.$router.push(`/${this.locale}/login`);
        },
    },
};
</script>

<style scoped>
.ks-user-account {
    background: #f4f4f4;
    padding: 32px 0 56px;
    min-height: 55vh;
}

.ks-container {
    width: min(1140px, 100%);
    margin: 0 auto;
    padding: 0 16px;
    box-sizing: border-box;
}

.ks-user-account-shell {
    display: grid;
    grid-template-columns: minmax(240px, 280px) 1fr;
    gap: 20px;
    align-items: start;
}

.ks-user-account-aside {
    background: #fff;
    border: 1px solid #e6e6e6;
    border-radius: 8px;
    padding: 20px 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    position: sticky;
    top: 88px;
}

.ks-user-account-user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 16px;
    margin-bottom: 12px;
    border-bottom: 1px solid #efefef;
}

.ks-user-account-avatar-wrap {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: #ececec;
    border: 2px solid #f5c400;
}

.ks-user-account-avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ks-user-account-user-meta {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.ks-user-account-user-meta strong {
    font-size: 15px;
    color: #111;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ks-user-account-user-meta span {
    font-size: 12px;
    color: #666;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ks-user-account-nav {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ks-user-account-nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 6px;
    color: #222;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    border: 0;
    background: transparent;
    width: 100%;
    text-align: inherit;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}

.ks-user-account-nav-link:hover,
.ks-user-account-nav-link.is-active {
    background: #fff8d6;
    color: #111;
}

.ks-user-account-nav-link--logout {
    margin-top: 8px;
    color: #a33;
}

.ks-user-account-nav-link--logout:hover {
    background: #fdecec;
    color: #811;
}

.ks-user-account-nav-ico {
    width: 22px;
    text-align: center;
    flex-shrink: 0;
    font-size: 15px;
    line-height: 1;
}

.ks-user-account-main {
    min-width: 0;
}

.ks-user-account--ar .ks-user-account-user-meta,
.ks-user-account--ar .ks-user-account-nav-link {
    text-align: right;
}

@media (max-width: 900px) {
    .ks-user-account-shell {
        grid-template-columns: 1fr;
    }

    .ks-user-account-aside {
        position: static;
    }

    .ks-user-account-nav {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ks-user-account-nav-link {
        flex: 1 1 auto;
        justify-content: center;
        min-width: calc(50% - 8px);
    }

    .ks-user-account-nav-link--logout {
        width: 100%;
    }
}
</style>

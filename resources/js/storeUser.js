import { createStore } from "vuex";
import { api, authApi,setLanguage } from "./axiosUser";
import router from "./routes";

const bootstrapSetting =
    typeof window !== "undefined" && window.__SITE_SETTINGS__
        ? window.__SITE_SETTINGS__
        : null;

const store = createStore({
    state: {
        setting: bootstrapSetting,
        user: null,
        languages: [],
        currentLanguage: "en",
        translations: {},

        adCurrent: null
  
    },
    mutations: {

        setAdCurrent(state, ad) {
          state.adCurrent = ad;
        },


        setUser(state, user) {
            state.user = { ...user };
        },

        setSetting(state, setting) {
            state.setting = setting;
        },

        setLanguages(state, languages) {
            state.languages = languages;
        },

        setCurrentLanguage(state, language) {
            state.currentLanguage = language;
        },

        setTranslations(state, translations) {
            state.translations = translations; // تعيين الترجمات
        },
    }, 
    actions: {
        fetchLanguages({ commit }) {
            return api
                .get("Languages/getAllItems")
                .then((response) => {
                    commit("setLanguages", response.data.items);
                })
                .catch((error) => {
                    console.log("❌ Error fetching languages:", error);
                });
        },

        fetchSetting({ commit, state }) {
            
            return api.get('basic/getSetting')
                .then(response => {
                    const items = response.data.items || {};
                    const bootstrap = state.setting && typeof state.setting === 'object'
                        ? state.setting
                        : {};
                    commit('setSetting', { ...bootstrap, ...items });
                })
                .catch((error) => {
                    console.log("❌ Error fetching settings:", error);
                });
        },

        fetchUser({ commit }) {
            let token = localStorage.getItem('token') || localStorage.getItem('authToken');

            if (!token) {
                return;
            }

            return authApi({
                method: 'get',
                url: 'check-auth',
                headers: {
                    Authorization: 'Bearer ' + token
                }
            }).then(() => {

                return authApi({
                    method: 'get',
                    url: 'UserAuth/getAuth',
                    headers: {
                        Authorization: 'Bearer ' + token
                    }
                }).then(response => {
                    const userData = response.data.items;
                    // إضافة userType للتمييز بين Admin و User
                    userData.userType = 'user';
                    commit('setUser', userData);
                    // حفظ في localStorage للاستخدام في الشات
                    localStorage.setItem('user', JSON.stringify(userData));
                    console.log('✅ User data saved to localStorage with userType: user');

                }).catch(error => {
                    localStorage.removeItem('token');
                    localStorage.removeItem('authToken');
                    const locale = localStorage.getItem('locale') || 'en';
                    router.push({ path: `/${locale}/login` });
                });
            }).catch(() => {
                localStorage.removeItem('token');
                localStorage.removeItem('authToken');
                const locale = localStorage.getItem('locale') || 'en';
                router.push({ path: `/${locale}/login` });
            });
        },
 


    
        async loadTranslations({ commit, state }, locale = null) {
            const selectedLocale = locale;

            let storedTranslations = localStorage.getItem(
                `translations_${selectedLocale}`
            ); 

            // if (storedTranslations) {
            //     commit("setTranslations", JSON.parse(storedTranslations));
            //     return;
            // }

             await setLanguage(locale); 
 
            try {
                const response = await api.get("Languages/translations", {
                    params: { locale: selectedLocale },
                });

                const data = response.data;
                localStorage.setItem(
                    `translations_${selectedLocale}`,
                    JSON.stringify(data)
                );
                commit("setTranslations", data);
            } catch (error) {
                console.log("❌ Error loading translations:", error);
            }
        },

        async changeLanguage({ dispatch, commit }, locale) {
            commit("setCurrentLanguage", locale);
            await dispatch("loadTranslations", locale); // تحميل الترجمات
            localStorage.setItem("locale", locale); // حفظ اللغة في localStorage
        },
    },
    getters: {
        getLanguages(state) {
            return state.languages;
        },
        getUser(state) {
            return state.user;
        },
        getSetting(state) {
            return state.setting;
        },
        isAuthenticated(state) {
            return !!state.user;
        },
        getCurrentLanguage(state) {
            return state.currentLanguage;
        },
        getTranslations(state) {
            return state.translations; // إرجاع الترجمات
        },
    },
});

export default store;

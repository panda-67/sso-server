import { startStimulusApp } from "@symfony/stimulus-bundle";
import axios from "axios";

axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// ✅ Auto-attach JWT to Authorization header (if it exists)
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("jwt");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error),
);

axios.interceptors.response.use(
  (response) => response,
  (error) => {
    const { config, response } = error;

    if (response && response.status === 401) {
      const isLoginRequest =
        config && config.url && config.url.includes("/login");

      if (!isLoginRequest) {
        window.location.href = "/"; // optional fallback
      }
    }

    return Promise.reject(error);
  },
);

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

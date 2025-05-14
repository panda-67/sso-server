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
    if (error.response && error.response.status === 401) {
      window.location.href = "/"; // optional fallback
    }
    return Promise.reject(error);
  },
);

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

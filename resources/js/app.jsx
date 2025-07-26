import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/inertia-react';

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.jsx');
    return pages[`./Pages/${name}.jsx`]().then(module => module.default);
  },
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />);
  },
});

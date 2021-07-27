import { writable } from 'svelte/store';

export const allowFlag = writable(Boolean(localStorage.getItem(`allow`)));
export const shodhanFlag = writable(Boolean(localStorage.getItem(`shodhan`)));

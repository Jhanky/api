import { clsx } from "clsx";
import { twMerge } from "tailwind-merge"

export function cn(...inputs) {
  return twMerge(clsx(inputs));
}

// Formatear moneda
export function formatCurrency(amount, currency = 'COP') {
  if (amount === null || amount === undefined) return '$0';

  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount);
}

// Formatear fecha
export function formatDate(date, options = {}) {
  if (!date) return '';

  const defaultOptions = {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  };

  const formatOptions = { ...defaultOptions, ...options };

  return new Intl.DateTimeFormat('es-CO', formatOptions).format(new Date(date));
}

// Formatear fecha corta
export function formatDateShort(date) {
  if (!date) return '';

  return new Intl.DateTimeFormat('es-CO', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  }).format(new Date(date));
}

// Formatear número
export function formatNumber(number, decimals = 0) {
  if (number === null || number === undefined) return '0';

  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  }).format(number);
}

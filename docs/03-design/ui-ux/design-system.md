# Design System - Label Generator System

Dokumen ini merupakan dokumentasi lengkap untuk design system Label Generator yang bertujuan untuk mendefinisikan visual guidelines, component patterns, dan interaction standards, yaitu: iOS-inspired design principles, animation patterns, dan VueUse utilities yang memberikan native-like user experience.

## Overview

Design system ini mengadopsi iOS design principles untuk memberikan experience yang familiar bagi pengguna dengan fokus pada:

- **Clarity** - Content yang jelas dan mudah dipahami
- **Deference** - UI yang tidak mengganggu konten utama
- **Depth** - Visual layers dengan glass effect dan shadows

## iOS Design Principles

### Spring Physics Animation

Semua transition dan animation menggunakan spring physics untuk memberikan feel yang natural dan bouncy:

```css
/* Spring easing function */
transition-timing-function: cubic-bezier(0.34, 1.56, 0.64, 1);
```

**Tailwind Implementation:**

```html
<div class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]">
    <!-- Content -->
</div>
```

### Press Feedback

Setiap interactive element memiliki scale-down effect saat di-tap untuk memberikan visual feedback:

| Element Type | Scale Value | Duration |
|--------------|-------------|----------|
| Button | `scale-[0.97]` | 200ms |
| Card | `scale-[0.98]` | 200ms |
| Icon Button | `scale-[0.95]` | 150ms |

**Implementation:**

```html
<button class="active:scale-[0.97] transition-transform duration-200">
    Click me
</button>
```

### Glass Effect (Frosted Glass)

Navbar dan footer menggunakan frosted glass effect untuk depth:

```css
/* Glass effect properties */
background: rgba(255, 255, 255, 0.8);
backdrop-filter: blur(24px);
border: 1px solid rgba(255, 255, 255, 0.5);
```

**Tailwind Implementation:**

```html
<!-- Light mode -->
<header class="bg-white/80 backdrop-blur-xl border-b border-gray-200/50">

<!-- Dark mode -->
<header class="dark:bg-zinc-900/80 dark:border-zinc-700/50">
```

### Haptic Feedback

Haptic feedback via Vibration API memberikan tactile response seperti native iOS:

| Action | Pattern | Duration |
|--------|---------|----------|
| Button press | `[10]` | 10ms |
| Toggle | `[10]` | 10ms |
| Submit | `[10]` | 10ms |
| Confirmation | `[10, 50, 10]` | 70ms total |

### Staggered Animations

Entrance animations dengan sequential timing untuk lists dan form fields:

```html
<!-- Staggered delay increments -->
<div class="transition-all delay-100 duration-500">Item 1</div>
<div class="transition-all delay-150 duration-500">Item 2</div>
<div class="transition-all delay-200 duration-500">Item 3</div>
<div class="transition-all delay-[250ms] duration-500">Item 4</div>
```

## VueUse Utilities

### useToggle

Cleaner API untuk toggle state dibanding manual ref:

```typescript
import { useToggle } from '@vueuse/core';

// Basic usage
const [isOpen, toggle] = useToggle(false);

// Toggle
toggle(); // isOpen = true
toggle(); // isOpen = false

// Set specific value
toggle(true); // isOpen = true
```

**Use Cases:**
- Password visibility toggle
- Mobile menu toggle
- Dropdown menu toggle

### useVibrate

Haptic feedback untuk iOS-like tactile response:

```typescript
import { useVibrate } from '@vueuse/core';

// Single vibration
const { vibrate } = useVibrate({ pattern: [10] });

// Confirmation pattern
const { vibrate: vibrateConfirm } = useVibrate({ pattern: [10, 50, 10] });

// Usage
function onButtonClick() {
    vibrate();
}
```

### onClickOutside

Detect clicks outside element untuk close dropdowns:

```typescript
import { onClickOutside } from '@vueuse/core';
import { ref } from 'vue';

const dropdownRef = ref<HTMLElement | null>(null);
const isOpen = ref(false);

onClickOutside(dropdownRef, () => {
    if (isOpen.value) {
        isOpen.value = false;
    }
});
```

## Color System

### Primary Colors

| Color | Light | Dark | Usage |
|-------|-------|------|-------|
| Blue 500 | `#3b82f6` | `#3b82f6` | Primary actions, links |
| Blue 600 | `#2563eb` | `#2563eb` | Hover states |
| Red 500 | `#ef4444` | `#ef4444` | Destructive actions |
| Red 600 | `#dc2626` | `#dc2626` | Destructive hover |

### Background Colors

| Element | Light | Dark |
|---------|-------|------|
| Page | `bg-gray-50` | `bg-zinc-900` |
| Card | `bg-white/80` | `bg-zinc-800/80` |
| Input | `bg-white` | `bg-zinc-700` |

### Text Colors

| Element | Light | Dark |
|---------|-------|------|
| Primary | `text-gray-900` | `text-white` |
| Secondary | `text-gray-600` | `text-gray-300` |
| Muted | `text-gray-500` | `text-gray-400` |
| Placeholder | `text-gray-400` | `text-gray-500` |

## Typography

### Font Families

| Font | Variable | Usage |
|------|----------|-------|
| Nunito Variable | `font-sans` | Body text, UI |
| Quicksand Variable | `font-display` | Headings, brand |

### Font Sizes

| Size | Class | Usage |
|------|-------|-------|
| XS | `text-xs` | Captions, hints |
| SM | `text-sm` | Body small, labels |
| Base | `text-base` | Body text |
| LG | `text-lg` | Subheadings |
| XL | `text-xl` | Section headers |
| 2XL | `text-2xl` | Page titles |

## Component Patterns

### Input Fields

```html
<input
    class="
        block w-full rounded-xl border border-gray-300 bg-white
        py-3 px-4 transition-all duration-200
        placeholder:text-gray-400
        focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20
        dark:border-zinc-600 dark:bg-zinc-700 dark:text-white
    "
    placeholder="Placeholder text"
/>
```

### Buttons

**Primary Button:**

```html
<button class="
    rounded-xl bg-linear-to-r from-blue-500 to-blue-600
    px-4 py-3 text-sm font-semibold text-white
    shadow-lg shadow-blue-500/25
    transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)]
    hover:from-blue-600 hover:to-blue-700 hover:shadow-blue-500/40
    active:scale-[0.97]
    disabled:opacity-70 disabled:cursor-not-allowed
">
    Button Text
</button>
```

**Ghost Button:**

```html
<button class="
    rounded-lg px-4 py-2 text-sm font-medium
    text-gray-600 transition-all duration-200
    hover:bg-gray-100 hover:text-gray-900
    active:scale-[0.97]
    dark:text-gray-300 dark:hover:bg-zinc-800 dark:hover:text-white
">
    Button Text
</button>
```

### Cards

```html
<div class="
    rounded-2xl border border-gray-200/50
    bg-white/80 p-8 shadow-xl backdrop-blur-xl
    dark:border-zinc-700/50 dark:bg-zinc-800/80
">
    <!-- Card content -->
</div>
```

### Dropdowns

```html
<div class="
    absolute right-0 mt-2 w-48 origin-top-right
    rounded-xl border border-gray-200/50
    bg-white/95 p-1 shadow-lg backdrop-blur-xl
    dark:border-zinc-700/50 dark:bg-zinc-800/95
">
    <!-- Dropdown items -->
</div>
```

## Transitions

### Vue Transitions

**Spring Transition:**

```html
<Transition
    enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
    enter-from-class="opacity-0 scale-95 -translate-y-2"
    enter-to-class="opacity-100 scale-100 translate-y-0"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100 scale-100 translate-y-0"
    leave-to-class="opacity-0 scale-95 -translate-y-2"
>
    <!-- Content -->
</Transition>
```

**Icon Rotate Transition:**

```html
<Transition
    mode="out-in"
    enter-active-class="transition-all duration-200 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
    enter-from-class="opacity-0 scale-75 rotate-12"
    enter-to-class="opacity-100 scale-100 rotate-0"
    leave-active-class="transition-all duration-150"
    leave-from-class="opacity-100 scale-100 rotate-0"
    leave-to-class="opacity-0 scale-75 -rotate-12"
>
    <Icon v-if="condition" />
    <OtherIcon v-else />
</Transition>
```

## Icons

Aplikasi menggunakan `lucide-vue-next` untuk icon library:

```typescript
import { Tag, User, Lock, Eye, EyeOff, Menu, X, LogOut } from 'lucide-vue-next';
```

**Standard Icon Props:**

```html
<Icon class="h-5 w-5" :stroke-width="2" />
```

## Accessibility

### Focus States

Semua interactive elements memiliki visible focus state:

```html
<button class="
    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
    dark:focus:ring-offset-zinc-800
">
```

### Color Contrast

- Text memenuhi WCAG AA standard (minimum 4.5:1 contrast ratio)
- Interactive elements memiliki sufficient color contrast

### Keyboard Navigation

- Tab navigation untuk semua interactive elements
- Enter/Space untuk aktivasi buttons
- Escape untuk close modals/dropdowns

---

**Author**: Zulfikar Hidayatullah  
**Last Updated**: 2025-12-02  
**Version**: 1.0  
**Status**: Complete

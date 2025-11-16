import { mount } from '@vue/test-utils';
import { describe, it, expect, beforeEach, vi } from 'vitest';
import Home from '@/Pages/Home.vue';
import { createRouter, createWebHistory } from 'vue-router';
import { createPinia, setActivePinia } from 'pinia';
import { useAuthStore } from '@/stores/auth';

vi.mock('@/services/api', () => ({
  default: { get: vi.fn() },
}));
import api from '@/services/api';

const router = createRouter({
  history: createWebHistory(),
  routes: [],
});

describe('Home.vue', () => {
  let wrapper;
  let pinia;

  beforeEach(async () => {
    pinia = createPinia();
    setActivePinia(pinia);
    const auth = useAuthStore();
    auth.user = { name: 'Test User', email: 'test@example.com' };

    api.get.mockReset();
    api.get.mockResolvedValue({ data: { data: [{ id: 1, name: 'Pizza' }] } });

    wrapper = mount(Home, {
      global: {
        plugins: [router, pinia],
      },
    });

    await wrapper.vm.$nextTick();
  });

  it('renders filter inputs', () => {
    const inputs = wrapper.findAll('input');
    expect(inputs).toHaveLength(2);
  });

  it('loads initial recipes', () => {
    expect(wrapper.vm.recipes.length).toBeGreaterThan(0);
  });

  it('applies filters and triggers API call', async () => {
    const nameInput = wrapper.find('input[placeholder="Search by name..."]');
    await nameInput.setValue('Burger');
    await new Promise((r) => setTimeout(r, 600));
    expect(wrapper.vm.filters.name).toBe('Burger');
    expect(api.get).toHaveBeenCalled();
  });

  it('handles API error gracefully', async () => {
    api.get.mockRejectedValueOnce({ response: { data: { message: 'Failed!' } } });
    await wrapper.vm.loadRecipes();
    expect(wrapper.vm.error).toBe('Failed!');
  });

  it('handles infinite scroll loading', async () => {
    wrapper.vm.hasMore = true;
    wrapper.vm.loading = false;
    api.get.mockResolvedValue({ data: { data: [{ id: 2, name: 'Burger' }] } });

    Object.defineProperty(window, 'innerHeight', { value: 1000, writable: true });
    Object.defineProperty(window, 'scrollY', { value: 700, writable: true });
    Object.defineProperty(document.documentElement, 'scrollHeight', { value: 2000, writable: true });

    await wrapper.vm.loadRecipes();
    expect(wrapper.vm.recipes.length).toBeGreaterThan(1);
  });
});

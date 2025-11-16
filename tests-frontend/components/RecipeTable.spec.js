import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import HomeRecipeTable from '@/Components/RecipeTable.vue'
import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/recipes/:id', name: 'recipe.show', component: { template: '<div>Show</div>' } },
  { path: '/recipes/:id/edit', name: 'recipe.edit', component: { template: '<div>Edit</div>' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

const recipes = [
  { id: 1, name: 'Pizza', cuisine_type: 'Italian', picture: 'pizza.jpg' },
  { id: 2, name: 'Sushi', cuisine_type: 'Japanese', picture: '' },
]

const pagination = { current_page: 1, last_page: 3 }

describe('HomeRecipeTable.vue', () => {
  let wrapper

  beforeEach(async () => {
    wrapper = mount(HomeRecipeTable, {
      props: { recipes, pagination },
      global: { plugins: [router] },
    })
    await router.isReady()
  })

  it('renders recipes with name and cuisine', () => {
    const rows = wrapper.findAll('tbody tr')
    expect(rows).toHaveLength(recipes.length)
    expect(rows[0].text()).toContain('Pizza')
    expect(rows[0].text()).toContain('Italian')
    expect(rows[1].text()).toContain('Sushi')
    expect(rows[1].text()).toContain('Japanese')
  })

  it('uses fallback image when picture is missing', () => {
    const images = wrapper.findAll('img')
    expect(images[0].attributes('src')).toContain('pizza.jpg')
    expect(images[1].attributes('src')).toBe(
      'https://placehold.co/48x48/dddddd/999999?text=No+Img'
    )
  })

  it('emits delete event when delete button is clicked', async () => {
    const deleteButton = wrapper.find('button.text-red-600')
    await deleteButton.trigger('click')
    expect(wrapper.emitted('delete')).toBeTruthy()
    expect(wrapper.emitted('delete')[0]).toEqual([recipes[0]])
  })

  it('emits page-change event when pagination buttons are clicked', async () => {
    const pageButtons = wrapper.findAll('div.flex.space-x-1 button')
    await pageButtons[1].trigger('click')
    expect(wrapper.emitted('page-change')[0]).toEqual([2])

    const prevButton = wrapper.find('button:disabled')
    expect(prevButton.attributes('disabled')).toBeDefined()
  })

  it('renders loading state when loading prop is true', async () => {
    await wrapper.setProps({ loading: true })
    const spinner = wrapper.find('.animate-spin')
    expect(spinner.exists()).toBe(true)
    expect(wrapper.text()).toContain('Loading recipes...')
  })
})

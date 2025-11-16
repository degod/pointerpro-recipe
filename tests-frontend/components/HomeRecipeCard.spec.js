import { mount, flushPromises } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import HomeRecipeCard from '@/Components/HomeRecipeCard.vue'

describe('HomeRecipeCard.vue', () => {
  const mockRecipes = [
    {
      id: 1,
      name: 'Pizza',
      cuisine_type: 'Italian',
      picture: 'pizza.jpg',
    },
    {
      id: 2,
      name: 'Sushi',
      cuisine_type: 'Japanese',
      picture: null,
    },
  ]

  it('renders recipes correctly', () => {
    const wrapper = mount(HomeRecipeCard, {
      props: { recipes: mockRecipes },
    })

    const recipeCards = wrapper.findAll('div.bg-white')
    expect(recipeCards.length).toBe(2)

    expect(recipeCards[0].find('h2').text()).toBe('Pizza')
    expect(recipeCards[0].find('p').text()).toBe('Italian')
    expect(recipeCards[0].find('img').attributes('src')).toContain('pizza.jpg')
    expect(recipeCards[1].find('h2').text()).toBe('Sushi')
    expect(recipeCards[1].find('p').text()).toBe('Japanese')
    expect(recipeCards[1].find('img').attributes('src')).toContain('https://placehold.co/120x120')
  })

  it('uses fallback image when image fails to load', async () => {
    const wrapper = mount(HomeRecipeCard, {
      props: { recipes: [mockRecipes[0]] },
    })
    const img = wrapper.find('img')
    await img.trigger('error')

    expect(img.attributes('src')).toBe('https://placehold.co/120x120/dddddd/999999?text=No+Img')
  })

  it('handles empty recipes array', () => {
    const wrapper = mount(HomeRecipeCard, {
      props: { recipes: [] },
    })

    expect(wrapper.findAll('div.bg-white').length).toBe(0)
  })
})

import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import Textarea from '@/Components/Textarea.vue'

describe('Textarea.vue', () => {
  it('renders label and textarea', () => {
    const wrapper = mount(Textarea, {
      props: { label: 'Description' },
    })

    const label = wrapper.find('label')
    expect(label.exists()).toBe(true)
    expect(label.text()).toBe('Description')

    const textarea = wrapper.find('textarea')
    expect(textarea.exists()).toBe(true)
    expect(textarea.attributes('placeholder')).toBe('')
    expect(textarea.attributes('required')).toBeUndefined()
  })

  it('applies placeholder and required props', () => {
    const wrapper = mount(Textarea, {
      props: { label: 'Notes', placeholder: 'Type here...', is_required: true },
    })

    const textarea = wrapper.find('textarea')
    expect(textarea.attributes('placeholder')).toBe('Type here...')
    expect(textarea.attributes('required')).toBeDefined()
  })

  it('renders validation message when provided', () => {
    const wrapper = mount(Textarea, {
      props: { label: 'Notes', message: ['Field is required'] },
    })

    const small = wrapper.find('small')
    expect(small.exists()).toBe(true)
    expect(small.text()).toBe('Field is required')
  })

  it('does not render validation message when message prop is empty', () => {
    const wrapper = mount(Textarea, {
        props: { label: 'Notes', message: [] },
    })
    const small = wrapper.find('small')
    expect(small.exists()).toBe(true)
    expect(small.text()).toBe('')
  })

  it('updates v-model correctly', async () => {
    const wrapper = mount(Textarea, {
      props: { label: 'Notes', modelValue: '' },
    })

    const textarea = wrapper.find('textarea')
    await textarea.setValue('Hello world')
    expect(textarea.element.value).toBe('Hello world')
  })
})

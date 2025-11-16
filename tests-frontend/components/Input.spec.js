import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import Input from '@/Components/Input.vue'

describe('Input.vue', () => {
  it('renders label correctly', () => {
    const wrapper = mount(Input, {
      props: { label: 'Username', modelValue: '' },
    })
    expect(wrapper.find('label').text()).toBe('Username')
  })

  it('renders input with correct type and placeholder', () => {
    const wrapper = mount(Input, {
      props: {
        label: 'Email',
        type: 'email',
        placeholder: 'Enter email',
        modelValue: '',
      },
    })

    const input = wrapper.find('input')
    expect(input.attributes('type')).toBe('email')
    expect(input.attributes('placeholder')).toBe('Enter email')
  })

  it('renders required attribute when is_required is true', () => {
    const wrapper = mount(Input, {
      props: { label: 'Password', is_required: true, modelValue: '' },
    })
    const input = wrapper.find('input')
    expect(input.attributes('required')).toBeDefined()
  })

  it('binds v-model correctly', async () => {
    let value = ''
    const wrapper = mount(Input, {
      props: {
        label: 'Name',
        modelValue: value,
        'onUpdate:modelValue': (val) => (value = val),
      },
    })

    const input = wrapper.find('input')
    await input.setValue('John Doe')
    expect(value).toBe('John Doe')
  })

  it('renders validation message when message prop is provided', () => {
    const wrapper = mount(Input, {
      props: { label: 'Username', message: ['Field is required'], modelValue: '' },
    })
    const small = wrapper.find('small')
    expect(small.exists()).toBe(true)
    expect(small.text()).toBe('Field is required')
  })

  it('does not render validation message when message prop is empty', () => {
    const wrapper = mount(Input, {
        props: { label: 'Username', message: [], modelValue: '' },
    })
    const small = wrapper.find('small')
    expect(small.exists()).toBe(true)
    expect(small.text()).toBe('')
  })
})

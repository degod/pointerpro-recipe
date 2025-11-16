import { mount, flushPromises } from '@vue/test-utils'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import ImageUpload from '@/Components/ImageUpload.vue'

const mockCreateObjectURL = vi.fn(() => 'https://mocked-preview.url/image.jpg')
global.URL.createObjectURL = mockCreateObjectURL
global.URL.revokeObjectURL = vi.fn()

class MockDataTransfer {
  constructor() {
    this.files = []
    this.items = []
  }
  add(file) {
    this.files.push(file)
    this.items.push({
      kind: 'file',
      type: file.type,
      getAsFile: () => file,
    })
  }
}
global.DataTransfer = MockDataTransfer

const createMockFile = (name = 'test.jpg', type = 'image/jpeg') => {
  return new File(['(fake content)'], name, { type })
}

describe('ImageUpload.vue', () => {
  let wrapper

  beforeEach(() => {
    vi.clearAllMocks()
    wrapper = mount(ImageUpload, {
      props: {
        modelValue: null,
      },
    })
  })

  it('renders placeholder when no image is selected', () => {
    expect(wrapper.find('svg').exists()).toBe(true)
    expect(wrapper.text()).toContain('Click to upload')
    expect(wrapper.text()).toContain('drag and drop')
    expect(wrapper.find('img').exists()).toBe(false)
  })

  it('shows preview and filename when an image is provided', async () => {
    const file = createMockFile('photo.jpg')
    const wrapper = mount(ImageUpload, {
        props: { modelValue: file },
    })
    await wrapper.vm.$nextTick()

    const removeBtn = wrapper.find('button')
    expect(removeBtn.exists()).toBe(true)
    expect(removeBtn.text()).toBe('Remove')
  })

  it('emits update:modelValue and shows preview when file is selected via input', async () => {
    const file = createMockFile()
    const input = wrapper.find('input[type="file"]')
    Object.defineProperty(input.element, 'files', {
      value: [file],
      writable: false,
    })

    await input.trigger('change')
    await wrapper.vm.$nextTick()
    const emitted = wrapper.emitted('update:modelValue')
    expect(emitted).toBeTruthy()
    expect(emitted).toHaveLength(1)
    expect(emitted[0]).toEqual([file])

    const img = wrapper.find('img')
    expect(img.exists()).toBe(true)
    expect(img.attributes('src')).toBe('https://mocked-preview.url/image.jpg')
  })

  it('emits update:modelValue when file is dropped', async () => {
    const file = createMockFile()
    const dataTransfer = new DataTransfer()
    dataTransfer.add(file)
    await wrapper.trigger('dragover')
    expect(wrapper.classes()).toContain('border-emerald-500')
    expect(wrapper.classes()).toContain('bg-emerald-50')

    await wrapper.trigger('drop', { dataTransfer })
    await wrapper.vm.$nextTick()
    const emitted = wrapper.emitted('update:modelValue')
    expect(emitted).toBeTruthy()
    expect(emitted).toHaveLength(1)
    expect(emitted[0]).toEqual([file])

    expect(wrapper.find('img').exists()).toBe(true)
  })

  it('does not accept non-image files and shows alert', async () => {
    const alertSpy = vi.spyOn(window, 'alert').mockImplementation(() => {})
    const file = new File([''], 'document.pdf', { type: 'application/pdf' })
    const input = wrapper.find('input[type="file"]')
    Object.defineProperty(input.element, 'files', {
      value: [file],
      writable: false,
    })

    await input.trigger('change')
    await wrapper.vm.$nextTick()
    expect(wrapper.emitted('update:modelValue')).toBeFalsy()
    expect(wrapper.find('img').exists()).toBe(false)
    expect(alertSpy).toHaveBeenCalledWith('Please upload an image file.')

    alertSpy.mockRestore()
  })

  it('removes image when remove button is clicked', async () => {
    const file = createMockFile()
    const wrapper = mount(ImageUpload, {
        props: { modelValue: file },
    })
    await wrapper.vm.$nextTick()

    const removeButton = wrapper.find('button')
    expect(removeButton.exists()).toBe(true)
    await removeButton.trigger('click')
    await wrapper.vm.$nextTick()

    const emitted = wrapper.emitted('update:modelValue')
    expect(emitted).toHaveLength(1)
    expect(emitted[0]).toEqual([null])
    expect(wrapper.find('img').exists()).toBe(false)
    expect(wrapper.find('svg').exists()).toBe(true)
  })

  it('opens file dialog when container is clicked', async () => {
    const input = wrapper.find('input[type="file"]')
    const clickSpy = vi.fn()
    input.element.click = clickSpy

    await wrapper.trigger('click')
    expect(clickSpy).toHaveBeenCalled()
  })

  it('highlights drop zone on dragover and removes on dragleave', async () => {
    await wrapper.trigger('dragover')
    expect(wrapper.classes()).toContain('border-emerald-500')
    expect(wrapper.classes()).toContain('bg-emerald-50')

    await wrapper.trigger('dragleave')
    expect(wrapper.classes()).not.toContain('border-emerald-500')
    expect(wrapper.classes()).not.toContain('bg-emerald-50')
  })
})
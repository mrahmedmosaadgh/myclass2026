import { computed, ref, watch } from 'vue'

const STORAGE_KEY = 'myclass2026.groupQuizPlayer.selectedVersion'

const versions = [
  {
    label: 'Version 1',
    value: 'ver1',
    description: 'Reusable Group Quiz player foundation'
  }
]

const selectedVersion = ref(resolveInitialVersion())

function resolveInitialVersion() {
  if (typeof window === 'undefined') return 'ver1'

  const saved = window.localStorage.getItem(STORAGE_KEY)
  return versions.some((version) => version.value === saved) ? saved : 'ver1'
}

watch(selectedVersion, (version) => {
  if (typeof window === 'undefined') return
  window.localStorage.setItem(STORAGE_KEY, version)
})

export function useGroupQuizVersion() {
  const selectedVersionMeta = computed(() => {
    return versions.find((version) => version.value === selectedVersion.value) || versions[0]
  })

  function setVersion(version) {
    if (!versions.some((item) => item.value === version)) {
      selectedVersion.value = 'ver1'
      return
    }

    selectedVersion.value = version
  }

  return {
    versions,
    selectedVersion,
    selectedVersionMeta,
    setVersion
  }
}

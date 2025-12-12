<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  userType: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(['success']);
const isPwd = ref(true);

const form = useForm({
  email: '',
  password: '',
  remember: false,
  user_type: props.userType,
});

const login = () => {
  form.transform(data => ({
    ...data,
    remember: form.remember ? 'on' : '',
  })).post(route('login'), {
    onFinish: () => {
      if (!form.hasErrors) {
        form.reset();
        emit('success');
      }
    },
  });
};
</script>

<template>
  <div class="w-full">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">{{ title }}</h2>
    
    <q-form @submit.prevent="login" class="q-gutter-md">
      <q-input
        filled
        v-model="form.email"
        label="User ID"
        :error="!!form.errors.email"
        :error-message="form.errors.email"
        class="rounded-borders"
      >
        <template v-slot:prepend>
          <q-icon name="person" />
        </template>
      </q-input>
      
      <q-input
        filled
        v-model="form.password"
        :type="isPwd ? 'password' : 'text'"
        label="Password"
        :error="!!form.errors.password"
        :error-message="form.errors.password"
        class="rounded-borders"
      >
        <template v-slot:prepend>
          <q-icon name="lock" />
        </template>
        <template v-slot:append>
          <q-icon
            :name="isPwd ? 'visibility_off' : 'visibility'"
            class="cursor-pointer"
            @click="isPwd = !isPwd"
          />
        </template>
      </q-input>
      
      <div class="flex items-center justify-between">
        <q-checkbox v-model="form.remember" label="Remember me" />
        <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
      </div>
      
      <q-btn
        type="submit"
        color="primary"
        :loading="form.processing"
        class="full-width q-py-sm q-mt-md"
        unelevated
        rounded
      >
        <span v-if="!form.processing">Login</span>
        <template v-slot:loading>
          <q-spinner-dots />
        </template>
      </q-btn>
    </q-form>
  </div>
</template>







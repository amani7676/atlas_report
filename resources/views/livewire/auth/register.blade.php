<div class="bg-white rounded-lg shadow-lg p-8">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">ثبت نام</h1>
        <p class="text-gray-600 mt-2">یک حساب کاربری جدید ایجاد کنید</p>
    </div>

    <form wire:submit="register">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                نام و نام خانوادگی
            </label>
            <input 
                type="text" 
                id="name" 
                wire:model="name"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="نام کامل"
                required
            >
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                ایمیل
            </label>
            <input 
                type="email" 
                id="email" 
                wire:model="email"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="example@email.com"
                required
            >
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">
                رمز عبور
            </label>
            <input 
                type="password" 
                id="password" 
                wire:model="password"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="حداقل ۸ کاراکتر"
                required
            >
            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">
                تکرار رمز عبور
            </label>
            <input 
                type="password" 
                id="password_confirmation" 
                wire:model="password_confirmation"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="تکرار رمز عبور"
                required
            >
        </div>

        <button 
            type="submit" 
            class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200 font-bold"
        >
            ثبت نام
        </button>
    </form>

    <div class="mt-6 text-center">
        <p class="text-gray-600">
            قبلاً ثبت نام کرده‌اید؟
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-bold">
                وارد شوید
            </a>
        </p>
    </div>
</div>

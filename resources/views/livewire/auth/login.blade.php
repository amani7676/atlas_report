<div class="bg-white rounded-lg shadow-lg p-8">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">ورود به سیستم</h1>
        <p class="text-gray-600 mt-2">برای دسترسی به پنل مدیریت وارد شوید</p>
    </div>

    <form wire:submit="login">
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
                placeholder="••••••••"
                required
            >
            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" wire:model="remember" class="mr-2">
                <span class="text-gray-600 text-sm">مرا به خاطر بسپار</span>
            </label>
        </div>

        <button 
            type="submit" 
            class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200 font-bold"
        >
            ورود
        </button>
    </form>

    <div class="mt-6 text-center">
        <p class="text-gray-600">
            حساب کاربری ندارید؟
            <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800 font-bold">
                ثبت نام کنید
            </a>
        </p>
    </div>
</div>

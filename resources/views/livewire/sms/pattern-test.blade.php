<div class="container mx-auto px-4 py-8" dir="rtl">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">تست ارسال پیامک الگویی</h2>

            <form wire:submit.prevent="sendTest">
                <!-- انتخاب الگو -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        انتخاب الگو <span class="text-red-500">*</span>
                    </label>
                    <select 
                        wire:model.live="selectedPattern" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- انتخاب الگو --</option>
                        @foreach($patterns as $pattern)
                            <option value="{{ $pattern->id }}">
                                {{ $pattern->title }} (کد: {{ $pattern->pattern_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('selectedPattern')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- نمایش متن الگو -->
                @if($patternText)
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            متن الگو:
                        </label>
                        <div class="text-gray-800 whitespace-pre-wrap font-medium">
                            {{ $patternText }}
                        </div>
                    </div>
                @endif

                <!-- ورودی متغیرها -->
                @if(count($variables) > 0)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            مقادیر متغیرها:
                        </label>
                        <div class="space-y-3">
                            @foreach($variables as $variable)
                                <div class="flex items-center gap-4">
                                    <label class="w-32 text-sm text-gray-600">
                                        {{ $variable['code'] }} ({{ $variable['title'] }}):
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model.live="variableValues.{{ $variable['index'] }}"
                                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="مقدار {{ $variable['title'] }} را وارد کنید"
                                    >
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- پیش‌نمایش پیام با متغیرهای جایگزین شده --}}
                    @if($previewMessage)
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200 border-r-4 border-r-blue-500">
                            <strong class="text-blue-700 block mb-3 flex items-center gap-2">
                                <i class="fas fa-eye"></i> پیش‌نمایش پیام ارسالی:
                            </strong>
                            <div class="bg-white p-4 rounded-lg border border-blue-200 text-gray-800 text-sm leading-relaxed">
                                {!! $previewMessage !!}
                            </div>
                            
                            @php
                                $variablesArray = [];
                                foreach ($variables as $variable) {
                                    $index = $variable['index'];
                                    $value = $variableValues[$index] ?? '';
                                    $variablesArray[] = $value;
                                }
                            @endphp
                            
                            @if(count($variablesArray) > 0)
                                <div class="mt-4 pt-4 border-t border-blue-200">
                                    <strong class="text-gray-600 text-xs block mb-2">متغیرهای ارسالی به API:</strong>
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @foreach($variables as $variable)
                                            @php
                                                $index = $variable['index'];
                                                $value = $variableValues[$index] ?? '';
                                            @endphp
                                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-mono">
                                                { {{ $index }} }: {{ $value ?: '[خالی]' }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="bg-gray-100 p-3 rounded">
                                        <strong class="text-gray-600 text-xs block mb-1">رشته ارسالی به API (با جداکننده ;):</strong>
                                        <code class="block mt-1 p-2 bg-white rounded text-xs text-left direction-ltr break-all">
                                            {{ implode(';', $variablesArray) }}
                                        </code>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif

                <!-- شماره تلفن -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        شماره تلفن گیرنده <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        wire:model="phone"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="09123456789"
                        maxlength="11"
                    >
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- دکمه ارسال -->
                <div class="flex justify-end gap-4">
                    <button 
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="sendTest">
                            ارسال تست
                        </span>
                        <span wire:loading wire:target="sendTest">
                            در حال ارسال...
                        </span>
                    </button>
                </div>
            </form>

            <!-- نمایش نتیجه -->
            @if($showResult && $result)
                <div class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">پاسخ API ملی پیامک</h3>
                    
                    <div class="space-y-4">
                        <!-- وضعیت -->
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-700">وضعیت:</span>
                            @if($result['success'] ?? false)
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                    ✅ موفق
                                </span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                    ❌ ناموفق
                                </span>
                            @endif
                        </div>

                        <!-- پیام -->
                        @if(isset($result['message']))
                            <div>
                                <span class="font-medium text-gray-700">پیام:</span>
                                <p class="mt-1 text-gray-800">{{ $result['message'] }}</p>
                            </div>
                        @endif

                        <!-- RecId -->
                        @if(isset($result['rec_id']))
                            <div>
                                <span class="font-medium text-gray-700">RecId:</span>
                                <p class="mt-1 text-gray-800 font-mono">{{ $result['rec_id'] }}</p>
                            </div>
                        @endif

                        <!-- کد پاسخ -->
                        @if(isset($result['response_code']))
                            <div>
                                <span class="font-medium text-gray-700">کد پاسخ:</span>
                                <p class="mt-1 text-gray-800 font-mono">{{ $result['response_code'] }}</p>
                            </div>
                        @endif

                        <!-- پاسخ خام -->
                        @if(isset($result['raw_response']))
                            <div>
                                <span class="font-medium text-gray-700">پاسخ خام API:</span>
                                <div class="mt-2 p-3 bg-white rounded border border-gray-300">
                                    <pre class="text-sm text-gray-800 whitespace-pre-wrap break-words">{{ $result['raw_response'] }}</pre>
                                </div>
                            </div>
                        @endif

                        <!-- پاسخ API (JSON) -->
                        @if(isset($result['api_response']))
                            <div>
                                <span class="font-medium text-gray-700">پاسخ API (JSON):</span>
                                <div class="mt-2 p-3 bg-white rounded border border-gray-300">
                                    <pre class="text-sm text-gray-800 whitespace-pre-wrap break-words">{{ is_array($result['api_response']) ? json_encode($result['api_response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $result['api_response'] }}</pre>
                                </div>
                            </div>
                        @endif

                        <!-- خطا -->
                        @if(isset($result['error']))
                            <div>
                                <span class="font-medium text-red-700">خطا:</span>
                                <p class="mt-1 text-red-800">{{ $result['error'] }}</p>
                            </div>
                        @endif

                        <!-- اطلاعات کامل (برای دیباگ) -->
                        <details class="mt-4">
                            <summary class="cursor-pointer text-sm font-medium text-gray-600 hover:text-gray-800">
                                نمایش اطلاعات کامل (برای دیباگ)
                            </summary>
                            <div class="mt-2 p-3 bg-white rounded border border-gray-300">
                                <pre class="text-xs text-gray-800 whitespace-pre-wrap break-words">{{ json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </details>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>



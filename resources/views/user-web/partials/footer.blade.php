@php($catalogVariant = request()->is('products'))
<footer class="w-full border-t border-zinc-200 {{ $catalogVariant ? 'bg-[#f3f3f3]' : 'bg-zinc-100' }} text-sm leading-relaxed">
    @if($catalogVariant)
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-8 px-8 py-12 md:flex-row">
            <div>
                <div class="mb-2 text-lg font-bold text-[#0d631b]">Heritage Harvest</div>
                <p class="max-w-xs text-xs text-zinc-500">© 2024 Heritage Harvest. Tôn vinh đặc sản Việt và câu chuyện vùng nguyên liệu.</p>
            </div>
            <div class="flex flex-wrap justify-center gap-6 md:gap-12">
                <a class="text-zinc-500 transition-colors hover:text-[#0d631b]" href="{{ route('user-web.story') }}">Bài viết</a>
                <a class="text-zinc-500 transition-colors hover:text-[#0d631b]" href="{{ route('user-web.checkout.show') }}">Giao hàng</a>
                <a class="text-zinc-500 transition-colors hover:text-[#0d631b]" href="{{ route('user-web.account.disputes') }}">Đổi trả & hỗ trợ</a>
                <a class="text-zinc-500 transition-colors hover:text-[#0d631b]" href="{{ route('user-web.regions') }}">Nguồn gốc</a>
                <a class="text-zinc-500 transition-colors hover:text-[#0d631b]" href="{{ route('user-web.account.profile') }}">Liên hệ</a>
            </div>
            <div class="flex space-x-4">
                @foreach(['public', 'eco'] as $icon)
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-surface-container-highest text-[#0d631b]">
                        @include('user-web.partials.icon', ['name' => $icon, 'class' => 'text-[18px]'])
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-8 py-16 md:grid-cols-4">
            <div class="space-y-4">
                <div class="text-2xl font-bold text-green-900">Heritage Harvest</div>
                <p class="text-zinc-600">Tôn vinh nghề nông và nghề thủ công Việt qua những sản phẩm được tuyển chọn từ các vùng nguyên liệu đặc sắc.</p>
            </div>
            <div class="space-y-4">
                <h5 class="font-bold text-green-900">Về chúng tôi</h5>
                <ul class="space-y-2">
                    <li><a class="inline-block text-zinc-600 transition-all hover:-translate-y-px hover:text-green-700" href="{{ route('user-web.story') }}">Bài viết cộng đồng</a></li>
                    <li><a class="inline-block text-zinc-600 transition-all hover:-translate-y-px hover:text-green-700" href="{{ route('user-web.regions') }}">Hành trình nguồn gốc</a></li>
                </ul>
            </div>
            <div class="space-y-4">
                <h5 class="font-bold text-green-900">Hỗ trợ</h5>
                <ul class="space-y-2">
                    <li><a class="inline-block text-zinc-600 transition-all hover:-translate-y-px hover:text-green-700" href="{{ route('user-web.checkout.show') }}">Chính sách giao hàng</a></li>
                    <li><a class="inline-block text-zinc-600 transition-all hover:-translate-y-px hover:text-green-700" href="{{ route('user-web.account.disputes') }}">Khiếu nại & hỗ trợ đơn</a></li>
                </ul>
            </div>
            <div class="space-y-4">
                <h5 class="font-bold text-green-900">Bản tin</h5>
                <p class="text-zinc-600">Nhận câu chuyện mùa vụ mới, gợi ý quà tặng và ưu đãi dành cho thành viên.</p>
                <form class="flex gap-2" action="{{ route('user-web.newsletter.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="source" value="footer">
                    <input class="w-full rounded-full border-none bg-white px-4 py-2 focus:ring-2 focus:ring-green-800" placeholder="Email của bạn" type="email" name="email">
                    <button class="rounded-full bg-green-800 p-2 text-white transition-opacity hover:opacity-80" type="submit" aria-label="Đăng ký bản tin">
                        @include('user-web.partials.icon', ['name' => 'arrow_forward'])
                    </button>
                </form>
            </div>
        </div>
        <div class="border-t border-zinc-200 px-8 py-6 text-center text-zinc-500">
            © 2024 Heritage Harvest. Tôn vinh đặc sản Việt và câu chuyện vùng nguyên liệu.
        </div>
    @endif
</footer>

<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Dashboard') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <!-- カレンダーshift.jsの読み込み -->
        <div id='shift'></div>
      </div>
      <!-- CreateModal -->
      <div class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-1000"
        id="createModal">
        <div class="bg-white rounded-lg shadow-lg max-w-md" style="width: 90%;">
          <div class="flex justify-between items-center p-4 border-b">
            <h5 class="text-lg font-medium">予定を登録</h5>
            <button type="button" class="text-gray-400 hover:text-gray-600" data-modal-dismiss="createModal">
              <span class="sr-only">Close</span>
              &times;
            </button>
          </div>
          <form name="create_form">
            <div class="p-4">
              <label for="create_date" class="block text-sm font-medium text-gray-700">登録予定日:</label>
              <input type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" id="create_date"
                name="date" value="">
              @foreach ($session_times as $key => $times)
                <div class="flex items-center py-1">
                  <input type="checkbox" name="session_times[]" id="tag{{ $key }}" class="form-check-input"
                    value="{{ $key }}">
                  <label for="tag{{ $key }}"
                    class="ml-2 text-sm font-medium text-gray-700">{{ $times['start_time'] }} ~
                    {{ $times['end_time'] }}</label>
                </div>
              @endforeach
            </div>
            <div class="flex justify-end p-4 border-t">
              <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded mr-2"
                data-modal-dismiss="createModal">閉じる</button>
              <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded" id="store-btn"
                data-modal-dismiss="createModal">保存する</button>
            </div>
          </form>
        </div>
      </div>

      <!-- ConfirmModal -->
      <div class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300"
        id="confirmModal">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
          <div class="flex justify-between items-center p-4 border-b">
            <h5 class="text-lg font-medium">予定確認</h5>
            <button type="button" class="text-gray-400 hover:text-gray-600" data-modal-dismiss="confirmModal">
              <span class="sr-only">Close</span>
              &times;
            </button>
          </div>
          <div class="p-4">
            <h5 class="text-lg font-medium">登録されている予定</h5>
            <div id="confirm-text"></div>
          </div>
          <div class="flex justify-end p-4 border-t">
            <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded"
              data-modal-dismiss="confirmModal">閉じる</button>
          </div>
        </div>
      </div>

      <!-- CautionModal -->
      <div class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300"
        id="cautionModal">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
          <div class="flex justify-between items-center p-4 border-b">
            <h5 class="text-lg font-medium">予定確認</h5>
            <button type="button" class="text-gray-400 hover:text-gray-600" data-modal-dismiss="cautionModal">
              <span class="sr-only">Close</span>
              &times;
            </button>
          </div>
          <div class="p-4">
            <h5 class="text-lg font-medium">提出期間を過ぎているため変更はできません</h5>
            <div id="caution-text"></div>
          </div>
          <div class="flex justify-end p-4 border-t">
            <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded"
              data-modal-dismiss="cautionModal">閉じる</button>
          </div>
        </div>
      </div>

      <!-- DeleteModal -->
      <div class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300"
        id="deleteModal">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
          <div class="flex justify-between items-center p-4 border-b">
            <h5 class="text-lg font-medium">削除確認</h5>
            <button type="button" class="text-gray-400 hover:text-gray-600" data-modal-dismiss="deleteModal">
              <span class="sr-only">Close</span>
              &times;
            </button>
          </div>
          <div class="p-4">
            <h5 class="text-lg font-medium">削除しますか？</h5>
            <div id="delete-text"></div>
          </div>
          <div class="flex justify-end p-4 border-t">
            <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded mr-2"
              data-modal-dismiss="deleteModal">閉じる</button>
            <button type="button" class="bg-red-500 text-white px-4 py-2 rounded" id="delete-btn"
              data-modal-dismiss="deleteModal">削除する</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>

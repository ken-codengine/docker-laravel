@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
  <h1>管理画面</h1>
@stop

@section('content')
  <x-adminlte-card>
    <!-- シフト提出期限を過ぎたら管理画面からカレンダーをロックしたい -->
    <!-- 年のプルダウン -->
    <p>カレンダーをロック</p>
    <label for="yearSelect">西暦</label>
    <select id="yearSelect">
      <?php for ($i = 2024; $i <= 2099; $i++): ?>
      <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
      <?php endfor; ?>
    </select>

    <div class="calendar-lock">
      <!-- 月のチェックボックス -->
      <?php for ($i = 1; $i <= 12; $i++): ?>
      <div>
        <input type="checkbox" class="monthCheckbox" id="monthCheckbox<?php echo $i; ?>">
        <label for="monthCheckbox<?php echo $i; ?>"><?php echo $i; ?>月</label>
      </div>
      <?php endfor; ?>
    </div>

    <!-- fullcalendar.js読み込み -->
    <div id='calendar'></div>

    <!-- 提出されたシフト確認・削除用confirmModal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">提出されたシフト</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            </button>
          </div>
          <div class="modal-body">
            <div id="confirm-text"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">閉じる</button>
            <button type="button" class="btn btn-danger" id="delete-btn" data-dismiss="modal">削除する</button>
          </div>
        </div>
      </div>
    </div>
  </x-adminlte-card>
  @push('js')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="/js/holiday_jp.js"></script>
    <script>
      // チェックされた月をDBに保存していく関数
      for (var i = 1; i <= 12; i++) {
        (function(i) { // DB保存用のmonthを即時関数で i の値をキャプチャ
          var checkbox = document.getElementById('monthCheckbox' + i);

          // チェックが切り替わるたびにそのyearとmonthをDB(lock_month)に送信
          checkbox.addEventListener('change', function() {
            // 10進数で年を取得
            var year = parseInt(document.getElementById('yearSelect').value, 10);
            var month = i;

            // ユーザー画面で表示中のカレンダーの年月（info.view.currentStart）と照合したい            
            // axiosでデータ送信
            axios.post('/your-endpoint', {
                checkboxState: this.checked,
                year: year, // ここで作成した日付を送信
                month: month // ここで作成した日付を送信
              })
              .then(function(response) {
                // console.log(response);
              })
              .catch(function(error) {
                // console.log(error);
              });
          });
        })(i);
      }

      document.addEventListener('DOMContentLoaded', function() {
        // const editModal = new Modal(document.getElementById('editModal'));
        var calendarEl = document.getElementById('calendar');
        let calendar = new FullCalendar.Calendar(calendarEl, {
          //表示テーマ
          themeSystem: 'bootstrap',
          contentHeight: '90vh',
          // plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin],
          initialView: 'dayGridMonth',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
          },
          // スマホでタップしたとき即反応
          selectLongPressDelay: 0,
          locale: 'ja',

          //祝日に赤spanタグを挿入
          dayCellContent: function(arg) {
            // console.log(arg);
            const date = new Date();
            date.setFullYear(
              arg.date.getFullYear(),
              arg.date.getMonth(),
              arg.date.getDate()
            );
            const holiday = holiday_jp.between(new Date(date), new Date(date));
            let hol_tag = document.createElement('span')
            if (holiday[0]) {
              hol_tag.innerHTML = `${arg.date.getDate()}`
              hol_tag.className = 'fc-day-hol';

              let arrayOfDomNodes = [hol_tag]
              return {
                domNodes: arrayOfDomNodes
              }
            } else {
              //日本語化の日表示を外す
              arg.dayNumberText = arg.dayNumberText.replace('日', '');
              return arg.dayNumberText;
            }
          },

          events: function(info, successCallback, failureCallback) {
            document.getElementById('yearSelect').value = info.start.getFullYear();

            // Laravelのイベント取得処理の呼び出し
            axios
              .post("/admin/home/events", {
                start_date: info.start.valueOf(),
                end_date: info.end.valueOf(),
              })
              .then((response) => {
                // 一旦全てのイベントを削除
                calendar.removeAllEvents();
                // // カレンダーに読み込み
                successCallback(response.data);
                // console.log(response.data);
                // console.log(info);
              })
              .catch(() => {
                // バリデーションエラーなど
                alert("取得に失敗しました");
              });
          },

          eventClick: function(info) {

            if (info.event.role = 'admin') {
              $('#deleteModal').modal('show');
              $('#delete-text').text(info.event.startStr + " " + info.event.title);
              // document.getElementById('edit_id').value = info.event.id;
              // document.getElementById('edit_text').value = info.event.extendedProps.text;
              // document.getElementById('edit_date').value = info.event.startStr;
              // console.log(info);
            }
            if (info.event.role = 'staff') {
              console.log(info);
              $('#confirmModal').modal('show');
              $('#confirm-text').text(info.event.startStr + " " + info.event.title);
              $(document).ready(function() {
                var close = $('#delete-btn');
                var deleteOnClick = function() {
                  axios
                    .post("/admin/home/destroy", {
                      id: info.event.id
                    })
                    .then((response) => {
                      var event = calendar.getEventById(response.data.id)
                      event.remove();
                    })
                    .catch(() => {
                      // バリデーションエラーなど
                      alert("取得に失敗しました");
                    });
                };

                // 保存ボタンによる送信、その後イベントの解除
                close.on('click', deleteOnClick);
                $('#confirmModal').on('hidden.bs.modal', function() {
                  console.log('hidden');
                  // 第二引数に値を指定する必要がある
                  close.off('click', deleteOnClick);
                });
              });
            }
          }

          // selectable: true,
          // select: function(info) {
          //   document.getElementById('create_date').value = info.startStr;
          //   $('#createModal').modal('show');

          //   const close = document.getElementById('store-btn');
          //   const saveOnClick = () => {
          //     // 値を日付型として取得
          //     const date = document.getElementById('create_date').valueAsDate
          //     const session_time = document.getElementById('session_time');
          //     const selected_option_text = session_time.options[session_time.selectedIndex].text;
          //     // const [start_time, end_time] = selected_option_text.split(' ~ ');
          //     const user = document.getElementById('create_user').value

          //     // Laravelのaxiosから登録処理の呼び出し
          //     axios
          //       .post("/admin/home/store", {
          //         start_date: info.start.valueOf(),
          //         end_date: info.end.valueOf(),
          //         date: date,
          //         text: text,
          //         user: user,
          //         session_time: session_time,
          //       })
          //       .then((response) => {
          //         // カレンダーに読み込み
          //         calendar.addEvent({
          //           // PHP側から受け取ったevent_idをeventObjectのidにセット
          //           id: response.data.id,
          //           title: response.data.title,
          //           color: response.data.color,
          //           start: response.data.start
          //         });
          //         //renderevent();はv3まで
          //         calendar.refetchEvents();
          //         // console.log(response);
          //       })
          //       .catch(() => {
          //         // バリデーションエラーなど
          //         alert("取得に失敗しました");
          //       });
          //   };

          //   //保存ボタンによる送信、その後イベントの解除
          //   close.addEventListener('click', saveOnClick)
          //   var createModalEl = document.getElementById('createModal')
          //   createModalEl.addEventListener('hidden.bs.modal', () => {
          //     //第二引数に値を指定する必要がある
          //     close.removeEventListener('click', saveOnClick);
          //   });
          // },
        });
        calendar.render();
      });
    </script>
  @endpush
@stop

@section('css')
  <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
  <script>
    // console.log('Hi!');
  </script>
@stop

@extends('dashboard')

@section('mock_tab_content')
    <script type='text/javascript'>

        @if ($sample_data)
            var current_example_data = {!! html_entity_decode($sample_data) !!};
        @else
            var current_example_data = null;
        @endif

        {{--var fake_dataset = JSON.parse('<?= json_encode($fake_dataset) ?>');--}}
        var fake_dataset = {};

        //  修改目前設定
        function onUserDataSelectorChanged() {
            let path = $('#userMockSettings-select option:selected').val()

            if (path == "separator") {
                return;
            }

            let parsed_url = new URL("https://www.pcone.com.tw/"+path);
            let searches = Array.from(new URLSearchParams(parsed_url.search));
            let inputs = $("input[name='exist_params[]']");

            for (let i = 0; i < inputs.length; i++) {
                if (searches.length > i) {
                    $(inputs[i]).val(searches[i][0]+"="+searches[i][1]);
                } else {
                    $(inputs[i]).val("");
                }
            }

            $.ajax({
                method: "POST",
                {{--url: "/gh/API/GetUserFakeData?device_hash=<?= $device_hash ?>&g8_user=<?= $g8_user ?>",--}}
                url: '',
                data: { path: path }
            }) .done(function(msg) {
                editor.set(msg.data)
            });
        }

        function onAPISourceChanged(selector) {
        }

        function onAPISectionChanged() {
            let section = $('#example-api-section option:selected').val()

            $.ajax({
                method: "GET",
                url: `/api/get_paths_data`,
                data: {
                    'section': section
                }

            }).done(function(response) {
                updateSelectDataQueryString(section)
                replaceSelectorData('#example-api-path', response.data, '選擇 API')
            });

        }

        function onAPIPathChanged() {
            let section = $('#example-api-section option:selected').val()
            let path = $('#example-api-path option:selected').val()

            $.ajax({
                method: "GET",
                url: `/api/get_samplenames_data`,
                data: {
                    'section': section,
                    'path': path
                }

            }).done(function(response) {
                updateSelectDataQueryString(section, path)
                replaceSelectorData('#example-api-sample', response.data, '選擇情境資料')
            });

        }

        function onAPISampleChanged() {
            let section = $('#example-api-section option:selected').val()
            let path = $('#example-api-path option:selected').val()
            let sample = $('#example-api-sample option:selected').val()

            current_example_data = null;

            $.ajax({
                method: "GET",
                url: `/api/get_sample_data`,
                data: {
                    'section': section,
                    'path': path,
                    'sample': sample
                }

            }).done(function(response) {
                updateSelectDataQueryString(section, path, sample)
                current_example_data = JSON.parse(response.data)
            });

        }

        function onApplyExampleButtonTapped() {
            let params = $("input[name='simple_params[]']").map(function(){return $(this).val();}).get();
            let path = $('#example-api-path option:selected').text()

            $.ajax({
                method: "POST",
                url: "/api/UpdateUserFakeData?{{ env('MOCK_USER_FIELDNAME', 'g8_user') }}={{ $mock_user }}",
                data: {
                    path: path,
                    params: params,
                    body: JSON.stringify(current_example_data)
                }
            })
            .done(function(result) {
                if (result.error) {
                    alert(`更新失敗：${result.error}`);
                } else {
                    alert(`更新成功`);
                }
            });
        }

        function onSaveButtonTapped() {
            let isNew = false
            let path = $('#userMockSettings-select option:selected').val()

            if ($("#newMockCheckbox").is(":checked")) {
                path = $("#newMockPath").val();
                isNew = true
            }
            if (!path || path == "separator") {
                alert("請(填入|選擇)有效的路徑");
                return
            }

            let params = $("input[name='exist_params[]']").map(function(){return $(this).val();}).get();

            $.ajax({
                method: "POST",
                url: "/api/UpdateUserFakeData?{{ env('MOCK_USER_FIELDNAME', 'g8_user') }}}={{ $mock_user }}",
                data: {
                    path: path,
                    body: editor.getText(),
                    params: params,
                }
            })
            .done(function(result) {
                if (result.errMsg) {
                    alert(`更新失敗：${result.errMsg}`);
                } else {
                    alert(`更新成功`);
                }
            });
        }

        function onOverrideFromExampleButtonTapped() {
            if (confirm("確定要覆蓋嗎？") && current_example_data) {
                editor.set(current_example_data);
            }
        }

        function onNewWindowShowExampleButtonTapped() {
            var win = window.open("", "_blank", "");
            win.document.body.innerHTML = `<pre id="json">${JSON.stringify(current_example_data, null, 4)}</pre>`
        }

        function updateSelectDataQueryString(section, path, sample) {
            if ('URLSearchParams' in window) {
                var searchParams = new URLSearchParams(window.location.search);
                let select = '0'
                if (section) {
                    select = select + ',' + section
                }
                if (path) {
                    select = select + ',' + path
                }
                if (sample) {
                    select = select + ',' + sample
                }
                searchParams.set("select", select)
                window.location.search = searchParams.toString();
            }
        }

        function replaceSelectorData(selectorName, data, defName) {
            let selector = $(selectorName)
            selector.html('');
            selector.append(new Option(defName, ""))
            for (let idx = 0; idx < data.length; idx++) {
                let d = data[idx];
                selector.append(new Option(d, idx))
            }
        }


    </script>

    <style>
        .w-15 {
            width: 15%
        }
    </style>


    <form>

        <div class="row p-2">

            <div class="mb-3">
                <div>
                    <h4>快速套用</h4>
                </div>
                <label for="exampleSelector" class="form-label">使用公版資料</label>
                <div class="input-group">
                    <label class="input-group-text w-15" for="example-api-source">資料來源</label>
                    <select id="example-api-source" class="form-select" onchange="onAPISourceChanged(this)">
                        @php
                            // <option value="">選擇資料來源</option>
                        @endphp
                        @foreach ($repo_names as $idx => $repo)
                            <option value="{{$idx}}" {{ $repo_index == $idx ? 'selected' : '' }}>{{ $repo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mt-1">
                    <label class="input-group-text w-15" for="example-api-section">API 群組</label>
                    <select id="example-api-section" class="form-select" onchange="onAPISectionChanged()">
                        <option value="">選擇 API 群組</option>
                        @foreach ($sections as $idx => $section)
                            <option value="{{$idx}}" {{ $section_index == $idx ? 'selected' : '' }}>{{ $section }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mt-1">
                    <label class="input-group-text w-15" for="example-api-path">API 路徑</label>
                    <select id="example-api-path" class="form-select" onchange="onAPIPathChanged()">
                        <option value="">選擇 API</option>
                        @foreach ($paths as $idx => $path)
                            <option value="{{$idx}}" {{ $path_index == $idx ? 'selected' : '' }}>{{ $path }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mt-1">
                    <label class="input-group-text w-15" style="width: 15%" for="example-api-sample">測試情境</label>
                    <select id="example-api-sample" class="form-select" onchange="onAPISampleChanged()">
                        <option value="">選擇情境資料</option>
                        @foreach ($sample_names as $idx => $sample)
                            <option value="{{$idx}}" {{ $sample_index == $idx ? 'selected' : '' }}>{{ $sample }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mt-1">
                    <button class="btn btn-outline-secondary" type="button" onclick="onApplyExampleButtonTapped()">直接套用</button>
                    <button class="btn btn-outline-secondary" type="button" onclick="onOverrideFromExampleButtonTapped()">覆蓋進階編輯區</button>
                    <button class="btn btn-outline-secondary" type="button" onclick="onNewWindowShowExampleButtonTapped()">新視窗開啟</button>
                </div>
                <div class="mt-3 row">
                    <div class="col-md-12 mt-1">
                        <small>根據參數回傳不同資料，直接填 key=value，只支援四組</small>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control mt-1" name="simple_params[]" placeholder="name=gh" >
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control mt-1" name="simple_params[]" placeholder="age=3" >
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control mt-1" name="simple_params[]" placeholder="money=0" >
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control mt-1" name="simple_params[]" placeholder="site=pcone" >
                    </div>
                </div>
            </div>

            <hr>

            <div>
                <h4>進階</h4>
            </div>
            <div class="mb-3">
                <label for="newMockCheckbox" class="form-label">新增 (填入 path，會蓋過已存在的)</label>
                <div class="input-group mb-3">
                    <div class="input-group-text">
                        <input id="newMockCheckbox" class="form-check-input mt-0" type="checkbox" value="" aria-label="Checkbox for following text input">
                    </div>
                    <span class="input-group-text">/</span>
                    <input type="text" id="newMockPath" class="form-control" aria-label="Text input with checkbox">
                </div>
            </div>

            <div class="mb-3">
                <label for="userMockSettings" class="form-label">修改目前設定</label>
                <select id="userMockSettings-select" class="form-select" onchange="onUserDataSelectorChanged();">
                    <option value="separator">選擇已存在的設定</option>
                    @php
                    $def_paths = [];
                    //$def_paths = ["/home/modules", "/initialization", "/initialization/page"];
                    //$def_paths = array_merge($def_paths, $user_mock_settings);
                    //$def_paths = array_unique($def_paths);
                    @endphp
                    @foreach ($def_paths as $path) {
                    <option value="{{ $path }}">{{ $path }}</option>
                    @endforeach
                </select>
                <div class="mt-3 row">
                    <div class="col-md-12 mt-1">
                        <small>根據參數回傳不同資料，直接填 key=value，只支援四組</small>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control mt-1" name="exist_params[]" placeholder="name=gh" >
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control mt-1" name="exist_params[]" placeholder="age=3" >
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control mt-1" name="exist_params[]" placeholder="money=0" >
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control mt-1" name="exist_params[]" placeholder="site=pcone" >
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="exampleFormControlTextarea1" class="form-label">假資料</label>
                <button type="button" class="btn btn-primary btn-sm" onclick="onSaveButtonTapped();">儲存</button>
                <div id="jsoneditor" style="width: 100%; height: 400px;"></div>
            </div>

        </div>

    </form>

    <script>
        const container = document.getElementById("jsoneditor")
        const options = {
            mode: 'code'
        }
        var editor;

        window.onload = function() {
            editor = new JSONEditor(container, options)
            editor.set({})
        };
    </script>

@endsection






@if($page->sectionOn('specs') && count($specRows))
  <section class="py-10 sm:py-14" data-lp-section="specs">
    <div class="max-w-2xl mx-auto px-4">
      @if(filled($d['specs']['heading'] ?? null))<h2 class="text-center text-xl sm:text-2xl font-bold">{{ $d['specs']['heading'] }}</h2>@endif
      <div class="mt-6 overflow-hidden rounded-xl border border-gray-200">
        <table class="w-full text-[15px]">
          @foreach($specRows as $row)
            <tr class="border-b border-gray-100 last:border-0">
              <th class="w-2/5 bg-gray-50 px-4 py-3 text-left font-semibold align-top">{{ $row['label'] }}</th>
              <td class="px-4 py-3 text-gray-700">{{ $row['value'] }}</td>
            </tr>
          @endforeach
        </table>
      </div>
    </div>
  </section>
@endif

<?php

namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class SongRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'     => 'required|min:3|unique:songs,name,NULL,id,album_id,' . $this->get('album_id') . ',user_id,"' . Auth::id(),
            'file_name'  => 'required',
            'genre_id' => 'required',
            'album_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'You already have a song titled "' . $this->get('name') . '" in the selected album.'
        ];
    }
}
